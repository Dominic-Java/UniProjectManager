<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\ClassroomInvitationMail;
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

        if ($user->hasRole('profesor') || $user->isAdmin()) {
            $classroomsQuery = Classroom::query()
                ->with('createdBy')
                ->withCount(['members', 'projects'])
                ->orderByDesc('is_active')
                ->orderByDesc('created_at');

            if (!$user->isAdmin()) {
                $classroomsQuery->where('created_by', $user->id);
            }

            $classrooms = $classroomsQuery->get();

            $invitations = collect();
        } else {
            $classrooms = Classroom::query()
                ->with('createdBy')
                ->whereHas('members', fn ($query) => $query->where('users.id', $user->id))
                ->where('is_active', true)
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
        abort_unless($request->user()?->hasRole('profesor') || $request->user()?->isAdmin(), 403);

        return view('classrooms.create', [
            'title' => 'Creeaza classroom',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('profesor') || $request->user()?->isAdmin(), 403);

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
            ->with('success', 'Classroom-ul a fost creat. Codul de acces este disponibil in pagina de detalii.');
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
            return back()->withErrors(['classroom_code' => 'Codul introdus nu este valid sau classroom-ul nu mai este activ.']);
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
            ->with('success', 'Te-ai alaturat classroom-ului cu succes.');
    }

    public function sendInvitation(Request $request, Classroom $classroom): RedirectResponse
    {
        abort_unless(ClassroomAccess::canManageClassroom($request->user(), $classroom), 403);

        if (!$classroom->is_active) {
            return back()->withErrors(['emails' => 'Classroom-ul este arhivat, de aceea nu mai pot fi trimise invitatii.']);
        }

        $validated = $request->validate([
            'email' => ['nullable', 'email'],
            'emails' => ['nullable', 'string', 'max:4000'],
            'message' => ['nullable', 'string', 'max:255'],
        ]);

        $emails = $this->extractInvitationEmails($validated['emails'] ?? null, $validated['email'] ?? null);
        if ($emails === []) {
            return back()
                ->withInput()
                ->withErrors(['emails' => 'Te rugam sa introduci cel putin o adresa de email valida.']);
        }

        $sentCount = 0;
        $skipped = [];

        foreach ($emails as $email) {
            $student = User::query()->where('email', $email)->first();
            if (!$student) {
                $skipped[] = $email . ' (nu exista cont)';
                continue;
            }

            if (!$student->hasRole('student')) {
                $skipped[] = $email . ' (nu are rol student)';
                continue;
            }

            $alreadyMember = DB::table('classroom_members')
                ->where('classroom_id', $classroom->id)
                ->where('user_id', $student->id)
                ->exists();

            if ($alreadyMember) {
                $skipped[] = $email . ' (deja in classroom)';
                continue;
            }

            $pendingInvite = ClassroomInvitation::query()
                ->where('classroom_id', $classroom->id)
                ->where('student_user_id', $student->id)
                ->where('status', 'pending')
                ->exists();

            if ($pendingInvite) {
                $skipped[] = $email . ' (invitatie activa deja existenta)';
                continue;
            }

            $invitation = ClassroomInvitation::create([
                'classroom_id' => $classroom->id,
                'student_user_id' => $student->id,
                'invited_by' => $request->user()->id,
                'status' => 'pending',
                'message' => $validated['message'] ?? null,
                'expires_at' => now()->addDays(7),
            ]);

            $this->sendInvitationMail(
                $student,
                $classroom,
                $request->user(),
                $invitation->message,
                $invitation->expires_at?->format('d.m.Y H:i')
            );

            AuditLogger::log('classrooms.invitation.send', $request->user(), 'classroom', $classroom->id, [
                'invitation_id' => $invitation->id,
                'student_user_id' => $student->id,
            ]);

            $sentCount++;
        }

        if ($sentCount === 0) {
            $details = implode(', ', array_slice($skipped, 0, 3));
            $suffix = count($skipped) > 3 ? ', ...' : '';

            return back()
                ->withInput()
                ->withErrors(['emails' => 'Nu am putut trimite invitatii. ' . $details . $suffix]);
        }

        $successMessage = $sentCount === 1
            ? 'Invitatia pentru classroom a fost trimisa.'
            : 'Au fost trimise ' . $sentCount . ' invitatii pentru classroom.';

        if ($skipped !== []) {
            $details = implode(', ', array_slice($skipped, 0, 3));
            $suffix = count($skipped) > 3 ? ', ...' : '';
            $successMessage .= ' Adrese omise: ' . $details . $suffix . '.';
        }

        return back()->with('success', $successMessage);
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
            return back()->with('error', 'Invitatia a expirat. Solicita o noua invitatie.');
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

    public function archive(Request $request, Classroom $classroom): RedirectResponse
    {
        abort_unless(ClassroomAccess::canManageClassroom($request->user(), $classroom), 403);

        if (!$classroom->is_active) {
            return back()->with('success', 'Classroom-ul este deja arhivat.');
        }

        DB::transaction(function () use ($classroom): void {
            $classroom->update(['is_active' => false]);

            ClassroomInvitation::query()
                ->where('classroom_id', $classroom->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'canceled',
                    'responded_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        AuditLogger::log('classrooms.archive', $request->user(), 'classroom', $classroom->id, [
            'name' => $classroom->name,
        ]);

        return back()->with('success', 'Classroom-ul a fost arhivat.');
    }

    public function destroy(Request $request, Classroom $classroom): RedirectResponse
    {
        abort_unless(ClassroomAccess::canManageClassroom($request->user(), $classroom), 403);

        $classroomId = $classroom->id;
        $classroomName = $classroom->name;
        $classroomCode = $classroom->code;

        $classroom->delete();

        AuditLogger::log('classrooms.delete', $request->user(), 'classroom', $classroomId, [
            'name' => $classroomName,
            'code' => $classroomCode,
        ]);

        return redirect()->route('classrooms.index')->with('success', 'Classroom-ul a fost eliminat.');
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

    private function sendInvitationMail(
        User $student,
        Classroom $classroom,
        User $invitedBy,
        ?string $messageText,
        ?string $expiresAt
    ): void {
        try {
            $classroom->loadMissing('createdBy');
            Mail::to($student->email)->send(new ClassroomInvitationMail(
                $student,
                $classroom,
                $invitedBy,
                $messageText,
                $expiresAt
            ));

            AuditLogger::log('classrooms.invitation_mail.sent', $invitedBy, 'classroom', $classroom->id, [
                'student_user_id' => $student->id,
            ]);
        } catch (\Throwable $exception) {
            report($exception);
            AuditLogger::log('classrooms.invitation_mail.failed', $invitedBy, 'classroom', $classroom->id, [
                'student_user_id' => $student->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function extractInvitationEmails(?string $bulkInput, ?string $singleInput): array
    {
        $raw = [];

        if ($bulkInput !== null && trim($bulkInput) !== '') {
            $raw[] = $bulkInput;
        }
        if ($singleInput !== null && trim($singleInput) !== '') {
            $raw[] = $singleInput;
        }

        if ($raw === []) {
            return [];
        }

        $emails = [];
        $parts = preg_split('/[\s,;]+/', implode("\n", $raw)) ?: [];

        foreach ($parts as $part) {
            $candidate = strtolower(trim($part));
            if ($candidate === '') {
                continue;
            }

            if (!filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $emails[$candidate] = true;
        }

        return array_keys($emails);
    }
}
