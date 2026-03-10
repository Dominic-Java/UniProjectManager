<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Models\Project;
use App\Services\Security\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MilestonesController extends Controller
{
    public function index(): View
    {
        $milestones = Milestone::with('project')
            ->orderByDesc('created_at')
            ->get();

        return view('milestones.index', [
            'title' => 'Milestones',
            'milestones' => $milestones,
        ]);
    }

    public function create(): View
    {
        return view('milestones.create', [
            'title' => 'Creeaza milestone',
            'projects' => Project::orderByDesc('created_at')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'weight' => ['required', 'numeric', 'min:0', 'max:999.99'],
        ]);

        $milestone = Milestone::create([
            'project_id' => $validated['project_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
            'weight' => $validated['weight'],
            'created_by' => $request->user()->id,
        ]);

        AuditLogger::log('milestones.create', $request->user(), 'milestone', $milestone->id);

        return redirect()->route('milestones.show', $milestone)->with('success', 'Milestone-ul a fost creat.');
    }

    public function show(Milestone $milestone): View
    {
        $milestone->load(['project', 'createdBy', 'deliverables']);

        return view('milestones.show', [
            'title' => 'Detalii milestone',
            'milestone' => $milestone,
        ]);
    }

    public function edit(Milestone $milestone): View
    {
        return view('milestones.edit', [
            'title' => 'Editeaza milestone',
            'milestone' => $milestone,
            'projects' => Project::orderByDesc('created_at')->get(),
        ]);
    }

    public function update(Request $request, Milestone $milestone): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'weight' => ['required', 'numeric', 'min:0', 'max:999.99'],
        ]);

        $milestone->update($validated);
        AuditLogger::log('milestones.update', $request->user(), 'milestone', $milestone->id);

        return redirect()->route('milestones.show', $milestone)->with('success', 'Milestone-ul a fost actualizat.');
    }

    public function destroy(Milestone $milestone): RedirectResponse
    {
        $milestoneId = $milestone->id;
        $milestone->delete();
        AuditLogger::log('milestones.delete', request()->user(), 'milestone', $milestoneId);

        return redirect()->route('milestones.index')->with('success', 'Milestone-ul a fost sters.');
    }
}
