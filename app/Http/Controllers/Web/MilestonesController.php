<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Models\Project;
use App\Services\Security\AuditLogger;
use App\Support\ClassroomAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MilestonesController extends Controller
{
    public function index(Request $request): View
    {
        $milestones = Milestone::with('project.classroom')
            ->whereHas('project', fn ($query) => ClassroomAccess::scopeVisibleProjects($query, $request->user()))
            ->orderByDesc('created_at')
            ->get();

        return view('milestones.index', [
            'title' => 'Milestones',
            'milestones' => $milestones,
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->hasRole('profesor'), 403);

        return view('milestones.create', [
            'title' => 'Creeaza milestone',
            'projects' => ClassroomAccess::scopeManageableProjects(
                Project::query()->openForParticipation(),
                auth()->user()
            )
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('profesor'), 403);

        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'weight' => ['required', 'numeric', 'min:0', 'max:999.99'],
        ]);

        $project = Project::findOrFail($validated['project_id']);
        abort_unless(ClassroomAccess::canManageProject($request->user(), $project), 403);
        if ($project->isLocked()) {
            return back()
                ->withInput()
                ->with('error', $this->projectLockedMessage());
        }

        $milestone = Milestone::create([
            'project_id' => $validated['project_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
            'weight' => $validated['weight'],
            'created_by' => $request->user()->id,
        ]);

        AuditLogger::log('milestones.create', $request->user(), 'milestone', $milestone->id);

        return redirect()->route('milestones.show', $milestone)->with('success', 'Etapa a fost creata cu succes.');
    }

    public function show(Milestone $milestone): View
    {
        $milestone->load(['project.classroom', 'createdBy', 'deliverables']);
        abort_unless($milestone->project && ClassroomAccess::canAccessProject(request()->user(), $milestone->project), 403);

        return view('milestones.show', [
            'title' => 'Detalii milestone',
            'milestone' => $milestone,
        ]);
    }

    public function edit(Milestone $milestone): View|RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('profesor'), 403);
        abort_unless($milestone->project && ClassroomAccess::canManageProject(auth()->user(), $milestone->project), 403);

        $milestone->loadMissing('project');
        if ($milestone->project?->isLocked()) {
            return redirect()
                ->route('milestones.show', $milestone)
                ->with('error', $this->projectLockedMessage());
        }

        return view('milestones.edit', [
            'title' => 'Editeaza milestone',
            'milestone' => $milestone,
            'projects' => ClassroomAccess::scopeManageableProjects(
                Project::query()
                    ->where(function ($query) use ($milestone) {
                        $query->openForParticipation()
                            ->orWhere('id', $milestone->project_id);
                    }),
                auth()->user()
            )
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function update(Request $request, Milestone $milestone): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('profesor'), 403);

        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'weight' => ['required', 'numeric', 'min:0', 'max:999.99'],
        ]);

        $project = Project::findOrFail($validated['project_id']);
        abort_unless(ClassroomAccess::canManageProject($request->user(), $project), 403);
        if ($project->isLocked()) {
            return back()
                ->withInput()
                ->with('error', $this->projectLockedMessage());
        }

        $milestone->update($validated);
        AuditLogger::log('milestones.update', $request->user(), 'milestone', $milestone->id);

        return redirect()->route('milestones.show', $milestone)->with('success', 'Modificarile etapei au fost salvate.');
    }

    public function destroy(Milestone $milestone): RedirectResponse
    {
        abort_unless(request()->user()?->hasRole('profesor'), 403);
        abort_unless($milestone->project && ClassroomAccess::canManageProject(request()->user(), $milestone->project), 403);

        $milestone->loadMissing('project');
        if ($milestone->project?->isLocked()) {
            return back()->with('error', $this->projectLockedMessage());
        }

        $milestoneId = $milestone->id;
        $milestone->delete();
        AuditLogger::log('milestones.delete', request()->user(), 'milestone', $milestoneId);

        return redirect()->route('milestones.index')->with('success', 'Etapa a fost eliminata.');
    }

    private function projectLockedMessage(): string
    {
        return 'Proiectul este inchis dupa termen. Etapele nu mai pot fi modificate.';
    }
}
