<?php

namespace App\Mail;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClassroomInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $student,
        public Classroom $classroom,
        public User $invitedBy,
        public ?string $message = null,
        public ?string $expiresAt = null
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Invitatie classroom - ' . $this->classroom->name)
            ->view('emails.classroom-invitation')
            ->with([
                'student' => $this->student,
                'classroom' => $this->classroom,
                'invitedBy' => $this->invitedBy,
                'messageText' => $this->message,
                'expiresAt' => $this->expiresAt,
                'sentAt' => now()->format('d.m.Y H:i'),
            ]);
    }
}
