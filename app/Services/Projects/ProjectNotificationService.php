<?php

namespace App\Services\Projects;

use App\Mail\NewProjectCreatedMail;
use App\Models\Project;
use App\Models\User;
use App\Services\Security\AuditLogger;
use Illuminate\Support\Facades\Mail;

class ProjectNotificationService
{
    public function notifyProjectCreated(Project $project, User $creator): int
    {
        $recipients = User::query()
            ->where('id', '!=', $creator->id)
            ->where('is_active', true)
            ->whereIn('role', ['student', 'profesor'])
            ->get(['id', 'email']);

        foreach ($recipients as $recipient) {
            Mail::to($recipient->email)->send(new NewProjectCreatedMail($project, $creator));
        }

        AuditLogger::log('projects.notification.sent', $creator, 'project', $project->id, [
            'recipient_count' => $recipients->count(),
            'subject' => $project->domain,
        ]);

        return $recipients->count();
    }
}
