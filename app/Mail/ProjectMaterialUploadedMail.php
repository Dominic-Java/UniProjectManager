<?php

namespace App\Mail;

use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProjectMaterialUploadedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Project $project,
        public ProjectMaterial $material,
        public User $uploadedBy
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Material nou: ' . $this->material->title . ' (' . $this->project->title . ')')
            ->view('emails.project-material-uploaded')
            ->with([
                'project' => $this->project,
                'material' => $this->material,
                'uploadedBy' => $this->uploadedBy,
            ]);
    }
}
