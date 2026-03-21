<?php

namespace App\Mail;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewProjectCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Project $project,
        public User $creator
    ) {}

    public function build(): self
    {
        $subject = 'Proiect nou: ' . $this->project->title;
        if (!empty($this->project->domain)) {
            $subject .= ' (' . $this->project->domain . ')';
        }

        return $this
            ->subject($subject)
            ->view('emails.project-created')
            ->with([
                'project' => $this->project,
                'creator' => $this->creator,
            ]);
    }
}
