<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Project;
use App\Services\Projects\ProjectNotificationService;
use App\Services\Projects\ProjectsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\Security\AuditLogger;
use App\Support\ClassroomAccess;

class ProjectsController extends Controller
{
    public function __construct(
        private ProjectsService $service,
        private ProjectNotificationService $projectNotificationService
    ) {}

    public function index(Request $request)
    {
        return view('projects.index', $this->service->getIndexData($request->user()));
    }

    public function create(Request $request)
    {
        abort_unless(auth()->user()?->hasRole('profesor'), 403);

        $classrooms = Classroom::query()
            ->where('created_by', $request->user()->id)
            ->orderBy('subject')
            ->orderBy('name')
            ->get();

        return view('projects.create', [
            'title' => 'Creeaza proiect',
            'classrooms' => $classrooms,
            'selected_classroom_id' => (int) $request->query('classroom', 0),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()?->hasRole('profesor'), 403);

        $validated = $request->validate([
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'visibility' => ['nullable', 'in:public,private'],
            'status' => ['nullable', 'in:draft,open,in_progress,closed,archived'],
            'min_team_size' => ['nullable', 'integer', 'min:1', 'max:20', 'lte:max_team_size'],
            'max_team_size' => ['nullable', 'integer', 'min:1', 'max:20', 'gte:min_team_size'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'deadline_at' => ['nullable', 'date', 'after_or_equal:start_date'],
            'code' => ['nullable', 'string', 'max:50', 'unique:projects,code'],
        ]);

        $classroom = Classroom::query()->findOrFail((int) $validated['classroom_id']);
        abort_unless(ClassroomAccess::canManageClassroom($request->user(), $classroom), 403);

        $validated['classroom_id'] = $classroom->id;
        $validated['domain'] = $classroom->subject;

        $result = $this->service->createProject($validated, auth()->id());

        if (!$result['ok']) {
            return back()->withInput()->with('error', $result['message']);
        }

        if (!empty($result['project_id'])) {
            $project = Project::query()->find($result['project_id']);
            if ($project) {
                try {
                    $this->projectNotificationService->notifyProjectCreated($project, $request->user());
                } catch (\Throwable $exception) {
                    report($exception);
                    AuditLogger::log('projects.notification.failed', $request->user(), 'project', $project->id, [
                        'error' => $exception->getMessage(),
                    ]);
                }
            }
        }

        AuditLogger::log('projects.create', $request->user(), 'project', $result['project_id'] ?? null, [
            'title' => $validated['title'],
            'domain' => $validated['domain'],
        ]);

        return redirect()->route('projects.index')->with('success', $result['message']);
    }

    public function show(Project $project)
    {
        abort_unless(ClassroomAccess::canAccessProject(request()->user(), $project), 403);

        $project->load(['classroom', 'teams', 'deliverables', 'milestones', 'requirements', 'staff', 'createdBy', 'materials.uploadedBy']);

        return view('projects.show', [
            'title' => 'Detalii proiect',
            'project' => $project,
            'can_manage' => ClassroomAccess::canManageProject(request()->user(), $project),
            'can_upload_materials' => ClassroomAccess::canUploadClasswork(request()->user(), $project),
        ]);
    }

    public function edit(Project $project)
    {
        abort_unless(ClassroomAccess::canManageProject(auth()->user(), $project), 403);

        $classrooms = Classroom::query()
            ->where('created_by', auth()->id())
            ->orderBy('subject')
            ->orderBy('name')
            ->get();

        return view('projects.edit', [
            'title' => 'Editeaza proiect',
            'project' => $project,
            'classrooms' => $classrooms,
        ]);
    }

    public function update(Request $request, Project $project)
    {
        abort_unless(ClassroomAccess::canManageProject(auth()->user(), $project), 403);

        $validated = $request->validate([
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
            'title' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('projects', 'code')->ignore($project->id)],
            'description' => ['required', 'string'],
            'domain' => [$project->classroom_id ? 'nullable' : 'required', 'string', 'max:120'],
            'visibility' => ['required', 'in:public,private'],
            'status' => ['required', 'in:draft,open,in_progress,closed,archived'],
            'min_team_size' => ['required', 'integer', 'min:1', 'max:20', 'lte:max_team_size'],
            'max_team_size' => ['required', 'integer', 'min:1', 'max:20', 'gte:min_team_size'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'deadline_at' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $classroomId = $validated['classroom_id'] ?? $project->classroom_id;
        if ($classroomId) {
            $classroom = Classroom::query()->findOrFail((int) $classroomId);
            abort_unless(ClassroomAccess::canManageClassroom($request->user(), $classroom), 403);

            $validated['classroom_id'] = $classroom->id;
            $validated['domain'] = $classroom->subject;
        }

        $project->update($validated);
        AuditLogger::log('projects.update', $request->user(), 'project', $project->id);

        return redirect()->route('projects.show', $project)->with('success', 'Proiectul a fost actualizat.');
    }

    public function destroy(Project $project)
    {
        abort_unless(ClassroomAccess::canManageProject(auth()->user(), $project), 403);

        $projectId = $project->id;
        $project->delete();
        AuditLogger::log('projects.delete', request()->user(), 'project', $projectId);

        return redirect()->route('projects.index')->with('success', 'Proiectul a fost sters.');
    }
}
