<?php

namespace App\Mail;

use App\Models\DeliverableSubmission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DeliverableSubmissionGradedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DeliverableSubmission $submission,
        public User $gradedBy,
        public bool $isUpdate = false
    ) {}

    public function build(): self
    {
        $title = $this->submission->deliverable?->title ?? 'Livrabil';

        return $this
            ->subject('Nota actualizata pentru livrabil: ' . $title)
            ->view('emails.deliverable-submission-graded')
            ->with([
                'submission' => $this->submission,
                'gradedBy' => $this->gradedBy,
                'isUpdate' => $this->isUpdate,
            ]);
    }
}

