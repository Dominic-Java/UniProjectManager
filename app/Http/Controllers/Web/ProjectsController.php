<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomGrade;
use App\Models\Project;
use App\Models\ProjectRequirement;
use App\Services\Projects\ProjectNotificationService;
use App\Services\Projects\ProjectsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
        abort_unless(auth()->user()?->hasRole('profesor') || auth()->user()?->isAdmin(), 403);

        $classroomsQuery = Classroom::query()
            ->orderBy('subject')
            ->orderBy('name');

        if (!$request->user()->isAdmin()) {
            $classroomsQuery->where('created_by', $request->user()->id);
        }

        $classrooms = $classroomsQuery->get();

        return view('projects.create', [
            'title' => 'Creeaza proiect',
            'classrooms' => $classrooms,
            'selected_classroom_id' => (int) $request->query('classroom', 0),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()?->hasRole('profesor') || auth()->user()?->isAdmin(), 403);
        $this->mergeDeadlineAtFrom24HourInputs($request);

        $validated = $request->validate([
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'visibility' => ['nullable', 'in:public,private'],
            'is_retake_project' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:draft,open,in_progress,closed,archived'],
            'min_team_size' => ['nullable', 'integer', 'min:1', 'max:20', 'lte:max_team_size'],
            'max_team_size' => ['nullable', 'integer', 'min:1', 'max:20', 'gte:min_team_size'],
            'start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:today', 'after_or_equal:start_date'],
            'deadline_at' => ['nullable', 'date', 'after_or_equal:now', 'after_or_equal:start_date'],
            'code' => ['nullable', 'string', 'max:50', 'unique:projects,code'],
        ]);

        $classroom = Classroom::query()->findOrFail((int) $validated['classroom_id']);
        abort_unless(ClassroomAccess::canManageClassroom($request->user(), $classroom), 403);

        $validated['classroom_id'] = $classroom->id;
        $validated['domain'] = $classroom->subject;
        $validated['is_retake_project'] = (bool) ($validated['is_retake_project'] ?? false);

        $result = $this->service->createProject($validated, auth()->id());

        if (!$result['ok']) {
            return back()->withInput()->with('error', $result['message']);
        }

        $retakeAudienceCount = 0;
        if (!empty($result['project_id'])) {
            $project = Project::query()->find($result['project_id']);
            if ($project) {
                $retakeAudienceCount = $this->syncRetakeAudience($project);

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
            'is_retake_project' => (bool) ($validated['is_retake_project'] ?? false),
            'retake_audience_count' => $retakeAudienceCount,
        ]);

        return redirect()->route('projects.index')->with('success', $result['message']);
    }

    public function show(Project $project)
    {
        abort_unless(ClassroomAccess::canAccessProject(request()->user(), $project), 403);

        $project->load(['classroom', 'teams', 'deliverables', 'milestones', 'requirements.createdBy', 'staff', 'createdBy', 'materials.uploadedBy']);

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

        $classroomsQuery = Classroom::query()
            ->orderBy('subject')
            ->orderBy('name');

        if (!auth()->user()?->isAdmin()) {
            $classroomsQuery->where('created_by', auth()->id());
        }

        $classrooms = $classroomsQuery->get();

        return view('projects.edit', [
            'title' => 'Editeaza proiect',
            'project' => $project,
            'classrooms' => $classrooms,
        ]);
    }

    public function update(Request $request, Project $project)
    {
        abort_unless(ClassroomAccess::canManageProject(auth()->user(), $project), 403);
        $this->mergeDeadlineAtFrom24HourInputs($request);

        $validated = $request->validate([
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
            'title' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('projects', 'code')->ignore($project->id)],
            'description' => ['required', 'string'],
            'domain' => [$project->classroom_id ? 'nullable' : 'required', 'string', 'max:120'],
            'visibility' => ['required', 'in:public,private'],
            'is_retake_project' => ['nullable', 'boolean'],
            'status' => ['required', 'in:draft,open,in_progress,closed,archived'],
            'min_team_size' => ['required', 'integer', 'min:1', 'max:20', 'lte:max_team_size'],
            'max_team_size' => ['required', 'integer', 'min:1', 'max:20', 'gte:min_team_size'],
            'start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:today', 'after_or_equal:start_date'],
            'deadline_at' => ['nullable', 'date', 'after_or_equal:now', 'after_or_equal:start_date'],
        ]);

        $classroomId = $validated['classroom_id'] ?? $project->classroom_id;
        if ($classroomId) {
            $classroom = Classroom::query()->findOrFail((int) $classroomId);
            abort_unless(ClassroomAccess::canManageClassroom($request->user(), $classroom), 403);

            $validated['classroom_id'] = $classroom->id;
            $validated['domain'] = $classroom->subject;
        }
        $validated['is_retake_project'] = (bool) ($validated['is_retake_project'] ?? false);

        $project->update($validated);
        $retakeAudienceCount = $this->syncRetakeAudience($project);
        AuditLogger::log('projects.update', $request->user(), 'project', $project->id);

        $success = 'Modificarile proiectului au fost salvate.';
        if ($project->isRetakeProject()) {
            $success .= ' Proiectul de restanta este vizibil pentru ' . $retakeAudienceCount . ' studenti restanti.';
        }

        return redirect()->route('projects.show', $project)->with('success', $success);
    }

    public function destroy(Project $project)
    {
        abort_unless(ClassroomAccess::canManageProject(auth()->user(), $project), 403);

        $projectId = $project->id;
        $project->delete();
        AuditLogger::log('projects.delete', request()->user(), 'project', $projectId);

        return redirect()->route('projects.index')->with('success', 'Proiectul a fost eliminat.');
    }

    public function storeRequirement(Request $request, Project $project)
    {
        abort_unless(ClassroomAccess::canManageProject($request->user(), $project), 403);

        if (!Schema::hasTable('project_requirements')) {
            return back()->withErrors([
                'requirement' => 'Tabela pentru cerinte proiect nu exista. Ruleaza migrarile.',
            ]);
        }

        if ($project->isLocked()) {
            return back()->with('error', 'Proiectul este inchis dupa termen. Nu mai poti adauga cerinte noi.');
        }

        $validated = $request->validate([
            'requirement_title' => ['required', 'string', 'max:200'],
            'requirement_description' => ['required', 'string', 'max:5000'],
            'send_requirement_email' => ['nullable', 'boolean'],
        ]);

        $nextVersion = ((int) ProjectRequirement::query()
            ->where('project_id', $project->id)
            ->max('version')) + 1;

        $requirement = ProjectRequirement::create([
            'project_id' => $project->id,
            'title' => $validated['requirement_title'],
            'description' => $validated['requirement_description'],
            'version' => max(1, $nextVersion),
            'is_active' => true,
            'created_by' => $request->user()->id,
            'created_at' => now(),
        ]);

        AuditLogger::log('projects.requirement.create', $request->user(), 'project_requirement', $requirement->id, [
            'project_id' => $project->id,
            'version' => $requirement->version,
        ]);

        $emailWasRequested = $request->boolean('send_requirement_email');
        $recipientCount = 0;
        if ($emailWasRequested) {
            try {
                $recipientCount = $this->projectNotificationService->notifyRequirementAdded(
                    $project,
                    $requirement,
                    $request->user()
                );
            } catch (\Throwable $exception) {
                report($exception);

                AuditLogger::log('projects.requirement.notification.failed', $request->user(), 'project_requirement', $requirement->id, [
                    'project_id' => $project->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $message = 'Cerinta a fost adaugata in proiect.';
        if ($emailWasRequested) {
            $message .= $recipientCount > 0
                ? ' Emailurile au fost trimise catre ' . $recipientCount . ' utilizatori.'
                : ' Nu s-au trimis emailuri (nu exista destinatari eligibili sau mail indisponibil).';
        }

        return back()->with('success', $message);
    }

    private function mergeDeadlineAtFrom24HourInputs(Request $request): void
    {
        $hasDateField = $request->exists('deadline_date');
        $hasTimeField = $request->exists('deadline_time');
        if (!$hasDateField && !$hasTimeField) {
            return;
        }

        $deadlineDate = trim((string) $request->input('deadline_date', ''));
        $deadlineTime = trim((string) $request->input('deadline_time', ''));

        if ($deadlineDate === '' && $deadlineTime === '') {
            $request->merge(['deadline_at' => null]);
            return;
        }

        if ($deadlineDate === '' || $deadlineTime === '') {
            throw ValidationException::withMessages([
                'deadline_time' => 'Completeaza atat data, cat si ora termenului limita (format HH:MM).',
            ]);
        }

        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $deadlineTime)) {
            throw ValidationException::withMessages([
                'deadline_time' => 'Ora introdusa nu este valida. Foloseste formatul HH:MM.',
            ]);
        }

        $request->merge([
            'deadline_at' => $deadlineDate . ' ' . $deadlineTime . ':00',
        ]);
    }

    private function syncRetakeAudience(Project $project): int
    {
        if (!Schema::hasTable('project_target_students')) {
            return 0;
        }

        DB::table('project_target_students')
            ->where('project_id', $project->id)
            ->delete();

        if (!$project->isRetakeProject() || !$project->classroom_id) {
            return 0;
        }

        $memberStudentIds = DB::table('classroom_members')
            ->where('classroom_id', $project->classroom_id)
            ->where('role', 'student')
            ->pluck('user_id');

        if ($memberStudentIds->isEmpty()) {
            return 0;
        }

        $studentIds = ClassroomGrade::query()
            ->where('classroom_id', $project->classroom_id)
            ->whereIn('student_user_id', $memberStudentIds)
            ->where('grade_value', '<', 5)
            ->pluck('student_user_id')
            ->unique()
            ->values();

        if ($studentIds->isEmpty()) {
            return 0;
        }

        $rows = $studentIds->map(fn (int $studentId): array => [
            'project_id' => $project->id,
            'student_user_id' => $studentId,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        DB::table('project_target_students')->insert($rows);

        return count($rows);
    }
}
