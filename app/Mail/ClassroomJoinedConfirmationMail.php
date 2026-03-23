<?php

namespace App\Mail;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClassroomJoinedConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $student,
        public Classroom $classroom,
        public string $joinedVia
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Confirmare inscriere classroom - ' . $this->classroom->name)
            ->view('emails.classroom-joined-confirmation')
            ->with([
                'student' => $this->student,
                'classroom' => $this->classroom,
                'joinedVia' => $this->joinedVia,
                'openedAt' => now()->format('d.m.Y H:i'),
            ]);
    }
}
