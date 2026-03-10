<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Projects\ProjectsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\Security\AuditLogger;

class ProjectsController extends Controller
{
    public function __construct(private ProjectsService $service) {}

    public function index()
    {
        return view('projects.index', $this->service->getIndexData());
    }

    public function create()
    {
        abort_unless(auth()->user()?->hasRole('admin', 'profesor'), 403);

        return view('projects.create', ['title' => 'Creeaza proiect']);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()?->hasRole('admin', 'profesor'), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'domain' => ['nullable', 'string', 'max:120'],
            'visibility' => ['nullable', 'in:public,private'],
            'status' => ['nullable', 'in:draft,open,in_progress,closed,archived'],
            'min_team_size' => ['nullable', 'integer', 'min:1', 'max:20', 'lte:max_team_size'],
            'max_team_size' => ['nullable', 'integer', 'min:1', 'max:20', 'gte:min_team_size'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'code' => ['nullable', 'string', 'max:50', 'unique:projects,code'],
        ]);

        $result = $this->service->createProject($validated, auth()->id());

        if (!$result['ok']) {
            return back()->withInput()->with('error', $result['message']);
        }

        AuditLogger::log('projects.create', $request->user(), 'project', null, [
            'title' => $validated['title'],
        ]);

        return redirect()->route('projects.index')->with('success', $result['message']);
    }

    public function show(Project $project)
    {
        $project->load(['teams', 'deliverables', 'milestones', 'requirements', 'staff', 'createdBy']);

        return view('projects.show', [
            'title' => 'Detalii proiect',
            'project' => $project,
        ]);
    }

    public function edit(Project $project)
    {
        abort_unless(auth()->user()?->hasRole('admin', 'profesor'), 403);

        return view('projects.edit', [
            'title' => 'Editeaza proiect',
            'project' => $project,
        ]);
    }

    public function update(Request $request, Project $project)
    {
        abort_unless(auth()->user()?->hasRole('admin', 'profesor'), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('projects', 'code')->ignore($project->id)],
            'description' => ['required', 'string'],
            'domain' => ['nullable', 'string', 'max:120'],
            'visibility' => ['required', 'in:public,private'],
            'status' => ['required', 'in:draft,open,in_progress,closed,archived'],
            'min_team_size' => ['required', 'integer', 'min:1', 'max:20', 'lte:max_team_size'],
            'max_team_size' => ['required', 'integer', 'min:1', 'max:20', 'gte:min_team_size'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $project->update($validated);
        AuditLogger::log('projects.update', $request->user(), 'project', $project->id);

        return redirect()->route('projects.show', $project)->with('success', 'Proiectul a fost actualizat.');
    }

    public function destroy(Project $project)
    {
        abort_unless(auth()->user()?->hasRole('admin', 'profesor'), 403);

        $projectId = $project->id;
        $project->delete();
        AuditLogger::log('projects.delete', request()->user(), 'project', $projectId);

        return redirect()->route('projects.index')->with('success', 'Proiectul a fost sters.');
    }
}
