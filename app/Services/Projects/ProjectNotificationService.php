<?php

namespace App\Services\Projects;

use App\Mail\NewProjectCreatedMail;
use App\Mail\ProjectRequirementAddedMail;
use App\Mail\ProjectMaterialUploadedMail;
use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Models\ProjectRequirement;
use App\Models\User;
use App\Services\Notifications\UserNotificationService;
use App\Services\Security\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class ProjectNotificationService
{
    public function __construct(private readonly UserNotificationService $userNotificationService) {}

    public function notifyProjectCreated(Project $project, User $creator): int
    {
        if ($project->classroom_id) {
            if ($project->isRetakeProject() && Schema::hasTable('project_target_students')) {
                $memberIds = DB::table('project_target_students')
                    ->where('project_id', $project->id)
                    ->pluck('student_user_id')
                    ->toArray();
            } else {
                $memberIds = DB::table('classroom_members')
                    ->where('classroom_id', $project->classroom_id)
                    ->pluck('user_id')
                    ->toArray();
            }

            $recipients = User::query()
                ->whereIn('id', $memberIds)
                ->where('id', '!=', $creator->id)
                ->where('is_active', true)
                ->get(['id', 'email']);
        } else {
            $recipients = User::query()
                ->where('id', '!=', $creator->id)
                ->where('is_active', true)
                ->whereIn('role', ['student', 'profesor'])
                ->get(['id', 'email']);
        }

        foreach ($recipients as $recipient) {
            Mail::to($recipient->email)->send(new NewProjectCreatedMail($project, $creator));

            $this->userNotificationService->notify(
                (int) $recipient->id,
                $project->isRetakeProject() ? 'Proiect nou de restanta' : 'Proiect nou disponibil',
                $project->title . ' - ' . ($project->domain ?: 'Materie'),
                route('projects.show', $project),
                $project->isRetakeProject() ? 'project.retake.created' : 'project.created'
            );
        }

        AuditLogger::log('projects.notification.sent', $creator, 'project', $project->id, [
            'recipient_count' => $recipients->count(),
            'subject' => $project->domain,
        ]);

        return $recipients->count();
    }

    public function notifyMaterialUploaded(Project $project, ProjectMaterial $material, User $uploadedBy): int
    {
        $recipientIds = $this->resolveMaterialRecipientIds($project);
        $recipientIds = array_values(array_unique(array_filter(
            $recipientIds,
            fn ($id): bool => (int) $id !== (int) $uploadedBy->id
        )));

        if (empty($recipientIds)) {
            AuditLogger::log('projects.material.notification.sent', $uploadedBy, 'project', $project->id, [
                'recipient_count' => 0,
                'material_id' => $material->id,
            ]);

            return 0;
        }

        $recipients = User::query()
            ->whereIn('id', $recipientIds)
            ->where('is_active', true)
            ->get(['id', 'email']);

        foreach ($recipients as $recipient) {
            Mail::to($recipient->email)->send(new ProjectMaterialUploadedMail($project, $material, $uploadedBy));

            $this->userNotificationService->notify(
                (int) $recipient->id,
                'Material nou incarcat',
                $material->title . ' in proiectul ' . $project->title,
                route('projects.show', $project),
                'project.material.uploaded'
            );
        }

        AuditLogger::log('projects.material.notification.sent', $uploadedBy, 'project', $project->id, [
            'recipient_count' => $recipients->count(),
            'material_id' => $material->id,
        ]);

        return $recipients->count();
    }

    public function notifyRequirementAdded(Project $project, ProjectRequirement $requirement, User $author): int
    {
        $recipientIds = $this->resolveMaterialRecipientIds($project);
        $recipientIds = array_values(array_unique(array_filter(
            $recipientIds,
            fn ($id): bool => (int) $id !== (int) $author->id
        )));

        if (empty($recipientIds)) {
            AuditLogger::log('projects.requirement.notification.sent', $author, 'project', $project->id, [
                'recipient_count' => 0,
                'requirement_id' => $requirement->id,
            ]);

            return 0;
        }

        $recipients = User::query()
            ->whereIn('id', $recipientIds)
            ->where('is_active', true)
            ->get(['id', 'email']);

        foreach ($recipients as $recipient) {
            Mail::to($recipient->email)->send(new ProjectRequirementAddedMail($project, $requirement, $author));

            $this->userNotificationService->notify(
                (int) $recipient->id,
                'Cerințe noi la proiect',
                $requirement->title . ' (v' . $requirement->version . ')',
                route('projects.show', $project),
                'project.requirement.added'
            );
        }

        AuditLogger::log('projects.requirement.notification.sent', $author, 'project', $project->id, [
            'recipient_count' => $recipients->count(),
            'requirement_id' => $requirement->id,
        ]);

        return $recipients->count();
    }

    private function resolveMaterialRecipientIds(Project $project): array
    {
        if ($project->classroom_id) {
            if ($project->isRetakeProject() && Schema::hasTable('project_target_students')) {
                return DB::table('project_target_students')
                    ->where('project_id', $project->id)
                    ->pluck('student_user_id')
                    ->map(fn ($value): int => (int) $value)
                    ->toArray();
            }

            return DB::table('classroom_members')
                ->where('classroom_id', $project->classroom_id)
                ->pluck('user_id')
                ->map(fn ($value): int => (int) $value)
                ->toArray();
        }

        $recipientIds = [];

        if (Schema::hasTable('teams') && Schema::hasTable('team_members')) {
            $teamMemberIds = DB::table('team_members')
                ->join('teams', 'teams.id', '=', 'team_members.team_id')
                ->where('teams.project_id', $project->id)
                ->pluck('team_members.user_id')
                ->map(fn ($value): int => (int) $value)
                ->toArray();
            $recipientIds = array_merge($recipientIds, $teamMemberIds);
        }

        if (Schema::hasTable('project_staff')) {
            $staffIds = DB::table('project_staff')
                ->where('project_id', $project->id)
                ->pluck('professor_user_id')
                ->map(fn ($value): int => (int) $value)
                ->toArray();
            $recipientIds = array_merge($recipientIds, $staffIds);
        }

        if ($project->created_by) {
            $recipientIds[] = (int) $project->created_by;
        }

        return $recipientIds;
    }
}
