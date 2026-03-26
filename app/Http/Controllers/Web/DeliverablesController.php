<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\DeliverableSubmissionGradedMail;
use App\Models\Deliverable;
use App\Models\DeliverableSubmission;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use App\Services\Notifications\UserNotificationService;
use App\Services\Security\AuditLogger;
use App\Support\ClassroomAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeliverablesController extends Controller
{
    public function __construct(private readonly UserNotificationService $userNotificationService) {}

    public function index(Request $request): View
    {
        $deliverables = Deliverable::with(['project', 'milestone'])
            ->whereHas('project', fn ($query) => ClassroomAccess::scopeVisibleProjects($query, $request->user()))
            ->orderByDesc('created_at')
            ->get();

        return view('deliverables.index', [
            'title' => 'Livrabile',
            'deliverables' => $deliverables,
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->hasRole('profesor') || auth()->user()?->isAdmin(), 403);

        return view('deliverables.create', [
            'title' => 'Creeaza livrabil',
            'projects' => ClassroomAccess::scopeManageableProjects(
                Project::query()->openForParticipation(),
                auth()->user()
            )
                ->orderByDesc('created_at')
                ->get(),
            'milestones' => Milestone::query()
                ->whereHas('project', function ($query): void {
                    $query->openForParticipation();
                    ClassroomAccess::scopeManageableProjects($query, auth()->user());
                })
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('profesor') || $request->user()?->isAdmin(), 403);

        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'milestone_id' => ['nullable', 'exists:milestones,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date', 'after_or_equal:now'],
            'submission_type' => ['required', 'in:file,link,both'],
            'max_points' => ['required', 'numeric', 'min:0', 'max:9999'],
        ]);

        $project = Project::findOrFail($validated['project_id']);
        abort_unless(ClassroomAccess::canManageProject($request->user(), $project), 403);
        if ($project->isLocked()) {
            return back()
                ->withInput()
                ->with('error', $this->projectLockedMessage());
        }

        if (!empty($validated['milestone_id'])) {
            $milestone = Milestone::find($validated['milestone_id']);
            if ($milestone && $milestone->project_id !== (int) $validated['project_id']) {
                return back()->withErrors(['milestone_id' => 'Etapa selectata nu apartine proiectului ales.']);
            }
        }

        $deliverable = Deliverable::create([
            'project_id' => $validated['project_id'],
            'milestone_id' => $validated['milestone_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
            'submission_type' => $validated['submission_type'],
            'max_points' => $validated['max_points'],
            'created_by' => $request->user()->id,
        ]);

        AuditLogger::log('deliverables.create', $request->user(), 'deliverable', $deliverable->id);

        return redirect()->route('deliverables.show', $deliverable)->with('success', 'Livrabilul a fost creat cu succes.');
    }

    public function show(Deliverable $deliverable): View
    {
        $deliverable->load(['project.classroom', 'milestone', 'createdBy', 'submissions.student', 'submissions.gradedBy']);
        abort_unless($deliverable->project && ClassroomAccess::canAccessProject(auth()->user(), $deliverable->project), 403);

        $mySubmission = null;
        $canGradeSubmissions = $deliverable->project
            && ClassroomAccess::canManageProject(auth()->user(), $deliverable->project);

        if (auth()->user()?->hasRole('student')) {
            $mySubmission = $deliverable->submissions
                ->firstWhere('student_user_id', auth()->id());
        }

        return view('deliverables.show', [
            'title' => 'Detalii livrabil',
            'deliverable' => $deliverable,
            'my_submission' => $mySubmission,
            'can_grade_submissions' => $canGradeSubmissions,
        ]);
    }

    public function submit(Request $request, Deliverable $deliverable): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('student'), 403);

        $deliverable->loadMissing('project');

        if (!$deliverable->project) {
            return back()->with('error', 'Livrabilul nu are un proiect asociat valid.');
        }
        abort_unless(ClassroomAccess::canAccessProject($request->user(), $deliverable->project), 403);

        if ($deliverable->project->isLocked()) {
            return back()->with('error', 'Proiectul este inchis dupa termen. Nu mai poti incarca acest livrabil.');
        }

        if ($deliverable->submission_type === 'link') {
            return back()->with('error', 'Acest livrabil accepta doar predare prin link.');
        }

        $validated = $request->validate([
            'submission_file' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,csv,zip,rar,7z,png,jpg,jpeg,gif,webp,bmp',
                'max:51200',
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $file = $validated['submission_file'];
        $originalName = (string) $file->getClientOriginalName();
        $sanitizedName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName) ?: 'fisier';
        $storedName = now()->format('Ymd_His') . '_' . Str::lower(Str::random(12)) . '_' . $sanitizedName;

        $storedPath = $file->storeAs(
            'deliverables/' . $deliverable->id . '/student_' . $request->user()->id,
            $storedName,
            'local'
        );

        $existing = DeliverableSubmission::query()
            ->where('deliverable_id', $deliverable->id)
            ->where('student_user_id', $request->user()->id)
            ->first();

        if ($existing && Storage::disk('local')->exists($existing->file_path)) {
            Storage::disk('local')->delete($existing->file_path);
        }

        $submissionUpdates = [
            'project_id' => $deliverable->project_id,
            'file_path' => $storedPath,
            'original_name' => $originalName,
            'mime_type' => $file->getClientMimeType(),
            'file_size_bytes' => $file->getSize() ?? 0,
            'notes' => $validated['notes'] ?? null,
            'submitted_at' => now(),
        ];

        if (Schema::hasColumn('deliverable_submissions', 'grade_points')) {
            // O noua incarcare invalideaza evaluarea anterioara.
            $submissionUpdates['grade_points'] = null;
            $submissionUpdates['grade_feedback'] = null;
            $submissionUpdates['graded_by_user_id'] = null;
            $submissionUpdates['graded_at'] = null;
        }

        $submission = DeliverableSubmission::updateOrCreate(
            [
                'deliverable_id' => $deliverable->id,
                'student_user_id' => $request->user()->id,
            ],
            $submissionUpdates
        );

        AuditLogger::log('deliverables.submission.upload', $request->user(), 'deliverable_submission', $submission->id, [
            'deliverable_id' => $deliverable->id,
            'project_id' => $deliverable->project_id,
        ]);

        return back()->with('success', 'Fisierul a fost incarcat cu succes.');
    }

    public function downloadSubmission(Request $request, DeliverableSubmission $submission): StreamedResponse|RedirectResponse
    {
        $user = $request->user();
        $submission->loadMissing('project.classroom');
        $canDownload = $submission->student_user_id === $user?->id;
        if (($user?->hasRole('profesor') || $user?->isAdmin()) && $submission->project) {
            $canDownload = ClassroomAccess::canManageProject($user, $submission->project);
        }
        if (!$canDownload) {
            abort(403);
        }

        if (!Storage::disk('local')->exists($submission->file_path)) {
            return back()->with('error', 'Fisierul cautat nu mai este disponibil in sistem.');
        }

        AuditLogger::log('deliverables.submission.download', $user, 'deliverable_submission', $submission->id, [
            'deliverable_id' => $submission->deliverable_id,
            'project_id' => $submission->project_id,
        ]);

        return Storage::disk('local')->download($submission->file_path, $submission->original_name);
    }

    public function cancelSubmission(Request $request, DeliverableSubmission $submission): RedirectResponse
    {
        $user = $request->user();

        if (!$user || $submission->student_user_id !== $user->id) {
            abort(403);
        }

        $submission->loadMissing('project');
        if (!$submission->project || !ClassroomAccess::canAccessProject($user, $submission->project)) {
            abort(403);
        }
        if ($submission->project?->isLocked()) {
            return back()->with('error', 'Proiectul este inchis dupa termen. Nu mai poti retrage predarea.');
        }

        if (Storage::disk('local')->exists($submission->file_path)) {
            Storage::disk('local')->delete($submission->file_path);
        }

        $submissionId = $submission->id;
        $deliverableId = $submission->deliverable_id;
        $submission->delete();

        AuditLogger::log('deliverables.submission.cancel', $user, 'deliverable_submission', $submissionId, [
            'deliverable_id' => $deliverableId,
        ]);

        return back()->with('success', 'Predarea a fost retrasa. Poti incarca o noua varianta cand esti pregatit.');
    }

    public function gradeSubmission(Request $request, DeliverableSubmission $submission): RedirectResponse
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('profesor') && !$user->isAdmin())) {
            abort(403);
        }

        if (!Schema::hasColumn('deliverable_submissions', 'grade_points')) {
            return back()->withErrors([
                'grade_points' => 'Functia de notare nu este disponibila in baza de date curenta. Ruleaza migrarile.',
            ]);
        }

        $submission->loadMissing(['project.classroom', 'deliverable', 'student']);

        if (!$submission->project || !ClassroomAccess::canManageProject($user, $submission->project)) {
            abort(403);
        }

        $maxPoints = (float) ($submission->deliverable?->max_points ?? 0);
        $validated = $request->validate([
            'grade_points' => ['required', 'numeric', 'min:0', 'max:' . $maxPoints],
            'grade_feedback' => ['nullable', 'string', 'max:3000'],
        ]);

        $wasGradedBefore = $submission->graded_at !== null || $submission->grade_points !== null;

        $submission->update([
            'grade_points' => round((float) $validated['grade_points'], 2),
            'grade_feedback' => $validated['grade_feedback'] ?? null,
            'graded_by_user_id' => $user->id,
            'graded_at' => now(),
        ]);
        $submission->refresh();
        $submission->loadMissing(['deliverable', 'project', 'student', 'gradedBy']);

        AuditLogger::log('deliverables.submission.grade', $user, 'deliverable_submission', $submission->id, [
            'deliverable_id' => $submission->deliverable_id,
            'project_id' => $submission->project_id,
            'student_user_id' => $submission->student_user_id,
            'grade_points' => $submission->grade_points,
            'was_update' => $wasGradedBefore,
        ]);

        $this->sendGradedSubmissionMail($submission, $user, $wasGradedBefore);

        return back()->with(
            'success',
            $wasGradedBefore
                ? 'Nota a fost actualizata, iar studentul a fost notificat pe email.'
                : 'Nota a fost salvata, iar studentul a fost notificat pe email.'
        );
    }

    public function edit(Deliverable $deliverable): View|RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('profesor') || auth()->user()?->isAdmin(), 403);
        abort_unless($deliverable->project && ClassroomAccess::canManageProject(auth()->user(), $deliverable->project), 403);

        $deliverable->loadMissing('project');
        if ($deliverable->project?->isLocked()) {
            return redirect()
                ->route('deliverables.show', $deliverable)
                ->with('error', $this->projectLockedMessage());
        }

        return view('deliverables.edit', [
            'title' => 'Editeaza livrabil',
            'deliverable' => $deliverable,
            'projects' => ClassroomAccess::scopeManageableProjects(
                Project::query()
                    ->where(function ($query) use ($deliverable) {
                        $query->openForParticipation()
                            ->orWhere('id', $deliverable->project_id);
                    }),
                auth()->user()
            )
                ->orderByDesc('created_at')
                ->get(),
            'milestones' => Milestone::query()
                ->where(function ($query) use ($deliverable) {
                    $query->whereHas('project', function ($projectQuery): void {
                        $projectQuery->openForParticipation();
                        ClassroomAccess::scopeManageableProjects($projectQuery, auth()->user());
                    })->orWhere('id', $deliverable->milestone_id);
                })
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function update(Request $request, Deliverable $deliverable): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('profesor') || $request->user()?->isAdmin(), 403);

        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'milestone_id' => ['nullable', 'exists:milestones,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date', 'after_or_equal:now'],
            'submission_type' => ['required', 'in:file,link,both'],
            'max_points' => ['required', 'numeric', 'min:0', 'max:9999'],
        ]);

        $project = Project::findOrFail($validated['project_id']);
        abort_unless(ClassroomAccess::canManageProject($request->user(), $project), 403);
        if ($project->isLocked()) {
            return back()
                ->withInput()
                ->with('error', $this->projectLockedMessage());
        }

        if (!empty($validated['milestone_id'])) {
            $milestone = Milestone::find($validated['milestone_id']);
            if ($milestone && $milestone->project_id !== (int) $validated['project_id']) {
                return back()->withErrors(['milestone_id' => 'Etapa selectata nu apartine proiectului ales.']);
            }
        }

        $deliverable->update([
            'project_id' => $validated['project_id'],
            'milestone_id' => $validated['milestone_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
            'submission_type' => $validated['submission_type'],
            'max_points' => $validated['max_points'],
        ]);

        AuditLogger::log('deliverables.update', $request->user(), 'deliverable', $deliverable->id);

        return redirect()->route('deliverables.show', $deliverable)->with('success', 'Modificarile livrabilului au fost salvate.');
    }

    public function destroy(Deliverable $deliverable): RedirectResponse
    {
        abort_unless(request()->user()?->hasRole('profesor') || request()->user()?->isAdmin(), 403);
        abort_unless($deliverable->project && ClassroomAccess::canManageProject(request()->user(), $deliverable->project), 403);

        $deliverable->loadMissing('project');
        if ($deliverable->project?->isLocked()) {
            return back()->with('error', $this->projectLockedMessage());
        }

        $deliverableId = $deliverable->id;
        $deliverable->delete();
        AuditLogger::log('deliverables.delete', request()->user(), 'deliverable', $deliverableId);

        return redirect()->route('deliverables.index')->with('success', 'Livrabilul a fost eliminat.');
    }

    private function projectLockedMessage(): string
    {
        return 'Proiectul este inchis dupa termen. Livrabilele nu mai pot fi modificate.';
    }

    private function sendGradedSubmissionMail(DeliverableSubmission $submission, User $gradedBy, bool $isUpdate): void
    {
        if (!$submission->student?->email) {
            return;
        }

        try {
            Mail::to($submission->student->email)->send(
                new DeliverableSubmissionGradedMail($submission, $gradedBy, $isUpdate)
            );

            $this->userNotificationService->notify(
                (int) $submission->student_user_id,
                $isUpdate ? 'Nota actualizata la livrabil' : 'Livrabil evaluat',
                ($submission->deliverable?->title ?? 'Livrabil') . ': ' . number_format((float) $submission->grade_points, 2) . ' puncte',
                $submission->deliverable
                    ? route('deliverables.show', $submission->deliverable)
                    : route('deliverables.index'),
                'deliverable.submission.graded'
            );

            AuditLogger::log('deliverables.submission.grade_mail.sent', $gradedBy, 'deliverable_submission', $submission->id, [
                'student_user_id' => $submission->student_user_id,
                'is_update' => $isUpdate,
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            AuditLogger::log('deliverables.submission.grade_mail.failed', $gradedBy, 'deliverable_submission', $submission->id, [
                'student_user_id' => $submission->student_user_id,
                'is_update' => $isUpdate,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
