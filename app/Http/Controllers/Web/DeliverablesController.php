<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Deliverable;
use App\Models\Milestone;
use App\Models\Project;
use App\Services\Security\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
            'projects' => Project::orderByDesc('created_at')->get(),
            'milestones' => Milestone::orderByDesc('created_at')->get(),
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
        $deliverable->load(['project', 'milestone', 'createdBy']);

        return view('deliverables.show', [
            'title' => 'Detalii livrabil',
            'deliverable' => $deliverable,
        ]);
    }

    public function edit(Deliverable $deliverable): View
    {
        abort_unless(auth()->user()?->hasRole('profesor'), 403);

        return view('deliverables.edit', [
            'title' => 'Editeaza livrabil',
            'deliverable' => $deliverable,
            'projects' => Project::orderByDesc('created_at')->get(),
            'milestones' => Milestone::orderByDesc('created_at')->get(),
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

        $deliverableId = $deliverable->id;
        $deliverable->delete();
        AuditLogger::log('deliverables.delete', request()->user(), 'deliverable', $deliverableId);

        return redirect()->route('deliverables.index')->with('success', 'Livrabilul a fost sters.');
    }
}
