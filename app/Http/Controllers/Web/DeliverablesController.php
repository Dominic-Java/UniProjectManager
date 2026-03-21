<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Deliverable;
use App\Models\DeliverableSubmission;
use App\Models\Milestone;
use App\Models\Project;
use App\Services\Security\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeliverablesController extends Controller
{
    public function index(): View
    {
        $deliverables = Deliverable::with(['project', 'milestone'])
            ->orderByDesc('created_at')
            ->get();

        return view('deliverables.index', [
            'title' => 'Livrabile',
            'deliverables' => $deliverables,
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->hasRole('profesor'), 403);

        return view('deliverables.create', [
            'title' => 'Creeaza livrabil',
            'projects' => Project::query()
                ->openForParticipation()
                ->orderByDesc('created_at')
                ->get(),
            'milestones' => Milestone::query()
                ->whereHas('project', fn ($query) => $query->openForParticipation())
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('profesor'), 403);

        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'milestone_id' => ['nullable', 'exists:milestones,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'submission_type' => ['required', 'in:file,link,both'],
            'max_points' => ['required', 'numeric', 'min:0', 'max:9999'],
        ]);

        $project = Project::findOrFail($validated['project_id']);
        if ($project->isLocked()) {
            return back()
                ->withInput()
                ->with('error', $this->projectLockedMessage());
        }

        if (!empty($validated['milestone_id'])) {
            $milestone = Milestone::find($validated['milestone_id']);
            if ($milestone && $milestone->project_id !== (int) $validated['project_id']) {
                return back()->withErrors(['milestone_id' => 'Milestone-ul selectat nu apartine proiectului.']);
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

        return redirect()->route('deliverables.show', $deliverable)->with('success', 'Livrabilul a fost creat.');
    }

    public function show(Deliverable $deliverable): View
    {
        $deliverable->load(['project', 'milestone', 'createdBy', 'submissions.student']);
        $mySubmission = null;

        if (auth()->user()?->hasRole('student')) {
            $mySubmission = $deliverable->submissions
                ->firstWhere('student_user_id', auth()->id());
        }

        return view('deliverables.show', [
            'title' => 'Detalii livrabil',
            'deliverable' => $deliverable,
            'my_submission' => $mySubmission,
        ]);
    }

    public function submit(Request $request, Deliverable $deliverable): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('student'), 403);

        $deliverable->loadMissing('project');

        if (!$deliverable->project) {
            return back()->with('error', 'Livrabilul nu are proiect asociat.');
        }

        if ($deliverable->project->isLocked()) {
            return back()->with('error', 'Proiectul este inchis dupa deadline. Nu mai poti incarca livrabilul.');
        }

        if ($deliverable->submission_type === 'link') {
            return back()->with('error', 'Acest livrabil accepta doar predare prin link.');
        }

        $validated = $request->validate([
            'submission_file' => ['required', 'file', 'max:51200'],
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

        $submission = DeliverableSubmission::updateOrCreate(
            [
                'deliverable_id' => $deliverable->id,
                'student_user_id' => $request->user()->id,
            ],
            [
                'project_id' => $deliverable->project_id,
                'file_path' => $storedPath,
                'original_name' => $originalName,
                'mime_type' => $file->getClientMimeType(),
                'file_size_bytes' => $file->getSize() ?? 0,
                'notes' => $validated['notes'] ?? null,
                'submitted_at' => now(),
            ]
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

        $canDownload = $user?->hasRole('profesor') || $submission->student_user_id === $user?->id;
        if (!$canDownload) {
            abort(403);
        }

        if (!Storage::disk('local')->exists($submission->file_path)) {
            return back()->with('error', 'Fisierul nu mai exista in storage.');
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
        if ($submission->project?->isLocked()) {
            return back()->with('error', 'Proiectul este inchis dupa deadline. Nu mai poti anula predarea.');
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

        return back()->with('success', 'Predarea a fost anulata. Poti incarca din nou cand esti pregatit.');
    }

    public function edit(Deliverable $deliverable): View|RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('profesor'), 403);

        $deliverable->loadMissing('project');
        if ($deliverable->project?->isLocked()) {
            return redirect()
                ->route('deliverables.show', $deliverable)
                ->with('error', $this->projectLockedMessage());
        }

        return view('deliverables.edit', [
            'title' => 'Editeaza livrabil',
            'deliverable' => $deliverable,
            'projects' => Project::query()
                ->where(function ($query) use ($deliverable) {
                    $query->openForParticipation()
                        ->orWhere('id', $deliverable->project_id);
                })
                ->orderByDesc('created_at')
                ->get(),
            'milestones' => Milestone::query()
                ->where(function ($query) use ($deliverable) {
                    $query->whereHas('project', fn ($projectQuery) => $projectQuery->openForParticipation())
                        ->orWhere('id', $deliverable->milestone_id);
                })
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function update(Request $request, Deliverable $deliverable): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('profesor'), 403);

        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'milestone_id' => ['nullable', 'exists:milestones,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'submission_type' => ['required', 'in:file,link,both'],
            'max_points' => ['required', 'numeric', 'min:0', 'max:9999'],
        ]);

        $project = Project::findOrFail($validated['project_id']);
        if ($project->isLocked()) {
            return back()
                ->withInput()
                ->with('error', $this->projectLockedMessage());
        }

        if (!empty($validated['milestone_id'])) {
            $milestone = Milestone::find($validated['milestone_id']);
            if ($milestone && $milestone->project_id !== (int) $validated['project_id']) {
                return back()->withErrors(['milestone_id' => 'Milestone-ul selectat nu apartine proiectului.']);
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

        return redirect()->route('deliverables.show', $deliverable)->with('success', 'Livrabilul a fost actualizat.');
    }

    public function destroy(Deliverable $deliverable): RedirectResponse
    {
        abort_unless(request()->user()?->hasRole('profesor'), 403);

        $deliverable->loadMissing('project');
        if ($deliverable->project?->isLocked()) {
            return back()->with('error', $this->projectLockedMessage());
        }

        $deliverableId = $deliverable->id;
        $deliverable->delete();
        AuditLogger::log('deliverables.delete', request()->user(), 'deliverable', $deliverableId);

        return redirect()->route('deliverables.index')->with('success', 'Livrabilul a fost sters.');
    }

    private function projectLockedMessage(): string
    {
        return 'Proiectul este inchis dupa deadline. Livrabilele nu mai pot fi modificate.';
    }
}
