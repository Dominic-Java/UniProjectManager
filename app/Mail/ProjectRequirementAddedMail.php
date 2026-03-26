<?php

namespace App\Mail;

use App\Models\Project;
use App\Models\ProjectRequirement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProjectRequirementAddedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Project $project,
        public ProjectRequirement $requirement,
        public User $author
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Cerinta noua: ' . $this->requirement->title . ' (' . $this->project->title . ')')
            ->view('emails.project-requirement-added')
            ->with([
                'project' => $this->project,
                'requirement' => $this->requirement,
                'author' => $this->author,
            ]);
    }
}
