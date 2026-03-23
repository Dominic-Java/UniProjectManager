<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\ClassroomJoinedConfirmationMail;
use App\Models\Classroom;
use App\Models\ClassroomInvitation;
use App\Models\User;
use App\Services\Security\AuditLogger;
use App\Support\ClassroomAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ClassroomsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->hasRole('profesor')) {
            $classrooms = Classroom::query()
                ->where('created_by', $user->id)
                ->withCount(['members', 'projects'])
                ->orderByDesc('created_at')
                ->get();

            $invitations = collect();
        } else {
            $classrooms = Classroom::query()
                ->whereHas('members', fn ($query) => $query->where('users.id', $user->id))
                ->withCount(['members', 'projects'])
                ->orderByDesc('created_at')
                ->get();

            $invitations = ClassroomInvitation::query()
                ->with(['classroom.createdBy', 'invitedBy'])
                ->where('student_user_id', $user->id)
                ->where('status', 'pending')
                ->orderByDesc('created_at')
                ->get();
        }

        return view('classrooms.index', [
            'title' => 'Classroom-uri',
            'classrooms' => $classrooms,
            'invitations' => $invitations,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->hasRole('profesor'), 403);

        return view('classrooms.create', [
            'title' => 'Creeaza classroom',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('profesor'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'subject' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
        ]);

        $classroom = DB::transaction(function () use ($validated, $request): Classroom {
            $classroom = Classroom::create([
                'code' => Classroom::generateCode(),
                'name' => trim($validated['name']),
                'subject' => trim($validated['subject']),
                'description' => $validated['description'] ?? null,
                'created_by' => $request->user()->id,
                'is_active' => true,
            ]);

            DB::table('classroom_members')->updateOrInsert(
                [
                    'classroom_id' => $classroom->id,
                    'user_id' => $request->user()->id,
                ],
                [
                    'role' => 'teacher',
                    'joined_at' => now(),
                    'updated_at' => now(),
                ]
            );

            return $classroom;
        });

        AuditLogger::log('classrooms.create', $request->user(), 'classroom', $classroom->id, [
            'subject' => $classroom->subject,
        ]);

        return redirect()
            ->route('classrooms.show', $classroom)
            ->with('success', 'Classroom-ul a fost creat. Codul de acces a fost generat automat.');
    }

    public function show(Request $request, Classroom $classroom): View
    {
        abort_unless(ClassroomAccess::canAccessClassroom($request->user(), $classroom), 403);

        $classroom->load([
            'createdBy',
            'members' => fn ($query) => $query->orderBy('first_name')->orderBy('last_name'),
            'projects' => fn ($query) => $query->orderByDesc('created_at'),
        ]);

        $invitations = ClassroomInvitation::query()
            ->with(['student', 'invitedBy'])
            ->where('classroom_id', $classroom->id)
            ->orderByDesc('created_at')
            ->get();

        return view('classrooms.show', [
            'title' => 'Detalii classroom',
            'classroom' => $classroom,
            'can_manage' => ClassroomAccess::canManageClassroom($request->user(), $classroom),
            'invitations' => $invitations,
        ]);
    }

    public function joinByCode(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('student'), 403);

        $validated = $request->validate([
            'classroom_code' => ['required', 'string', 'max:24'],
        ]);

        $code = strtoupper(trim($validated['classroom_code']));
        $classroom = Classroom::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$classroom) {
            return back()->withErrors(['classroom_code' => 'Cod invalid sau classroom inactiv.']);
        }

        $alreadyMember = DB::table('classroom_members')
            ->where('classroom_id', $classroom->id)
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($alreadyMember) {
            return back()->with('success', 'Esti deja inscris in acest classroom.');
        }

        DB::table('classroom_members')->insert([
            'classroom_id' => $classroom->id,
            'user_id' => $request->user()->id,
            'role' => 'student',
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        AuditLogger::log('classrooms.join_by_code', $request->user(), 'classroom', $classroom->id);
        $this->sendJoinConfirmationMail($request->user(), $classroom, 'code');

        return redirect()
            ->route('classrooms.show', $classroom)
            ->with('success', 'Ai intrat in classroom cu succes.');
    }

    public function sendInvitation(Request $request, Classroom $classroom): RedirectResponse
    {
        abort_unless(ClassroomAccess::canManageClassroom($request->user(), $classroom), 403);

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'message' => ['nullable', 'string', 'max:255'],
        ]);

        $student = User::query()->where('email', strtolower($validated['email']))->first();
        if (!$student) {
            return back()->withErrors(['email' => 'Nu exista utilizator cu acest email.']);
        }
        if (!$student->hasRole('student')) {
            return back()->withErrors(['email' => 'Invitatiile pentru classroom pot fi trimise doar catre studenti.']);
        }

        $alreadyMember = DB::table('classroom_members')
            ->where('classroom_id', $classroom->id)
            ->where('user_id', $student->id)
            ->exists();

        if ($alreadyMember) {
            return back()->withErrors(['email' => 'Studentul este deja in classroom.']);
        }

        $pendingInvite = ClassroomInvitation::query()
            ->where('classroom_id', $classroom->id)
            ->where('student_user_id', $student->id)
            ->where('status', 'pending')
            ->exists();

        if ($pendingInvite) {
            return back()->withErrors(['email' => 'Exista deja o invitatie activa pentru acest student.']);
        }

        $invitation = ClassroomInvitation::create([
            'classroom_id' => $classroom->id,
            'student_user_id' => $student->id,
            'invited_by' => $request->user()->id,
            'status' => 'pending',
            'message' => $validated['message'] ?? null,
            'expires_at' => now()->addDays(7),
        ]);

        AuditLogger::log('classrooms.invitation.send', $request->user(), 'classroom', $classroom->id, [
            'invitation_id' => $invitation->id,
            'student_user_id' => $student->id,
        ]);

        return back()->with('success', 'Invitatia in classroom a fost trimisa.');
    }

    public function respondInvitation(Request $request, ClassroomInvitation $invitation): RedirectResponse
    {
        if ($invitation->student_user_id !== $request->user()?->id) {
            abort(403);
        }

        if ($invitation->status !== 'pending') {
            return back()->with('error', 'Invitatia a fost deja procesata.');
        }

        $validated = $request->validate([
            'action' => ['required', 'in:accept,reject'],
        ]);

        if (
            $validated['action'] === 'accept'
            && $invitation->expires_at
            && $invitation->expires_at->isPast()
        ) {
            return back()->with('error', 'Invitatia a expirat.');
        }

        $status = $validated['action'] === 'accept' ? 'accepted' : 'rejected';

        if ($status === 'accepted') {
            DB::table('classroom_members')->updateOrInsert(
                [
                    'classroom_id' => $invitation->classroom_id,
                    'user_id' => $request->user()->id,
                ],
                [
                    'role' => 'student',
                    'joined_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $classroom = Classroom::query()->find($invitation->classroom_id);
            if ($classroom) {
                $this->sendJoinConfirmationMail($request->user(), $classroom, 'invitation');
            }
        }

        $invitation->update([
            'status' => $status,
            'responded_at' => now(),
        ]);

        AuditLogger::log('classrooms.invitation.respond', $request->user(), 'classroom', $invitation->classroom_id, [
            'status' => $status,
            'invitation_id' => $invitation->id,
        ]);

        return back()->with('success', 'Invitatia a fost ' . ($status === 'accepted' ? 'acceptata' : 'respinsa') . '.');
    }

    public function cancelInvitation(Request $request, ClassroomInvitation $invitation): RedirectResponse
    {
        $invitation->loadMissing('classroom');
        abort_unless(
            $invitation->classroom && ClassroomAccess::canManageClassroom($request->user(), $invitation->classroom),
            403
        );

        if ($invitation->status !== 'pending') {
            return back()->with('error', 'Invitatia nu mai este activa.');
        }

        $invitation->update([
            'status' => 'canceled',
            'responded_at' => now(),
        ]);

        AuditLogger::log('classrooms.invitation.cancel', $request->user(), 'classroom', $invitation->classroom_id, [
            'invitation_id' => $invitation->id,
        ]);

        return back()->with('success', 'Invitatia a fost anulata.');
    }

    private function sendJoinConfirmationMail(User $student, Classroom $classroom, string $joinedVia): void
    {
        try {
            $classroom->loadMissing('createdBy');
            Mail::to($student->email)->send(new ClassroomJoinedConfirmationMail($student, $classroom, $joinedVia));
            AuditLogger::log('classrooms.join_confirmation.sent', $student, 'classroom', $classroom->id, [
                'joined_via' => $joinedVia,
            ]);
        } catch (\Throwable $exception) {
            report($exception);
            AuditLogger::log('classrooms.join_confirmation.failed', $student, 'classroom', $classroom->id, [
                'joined_via' => $joinedVia,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
