<?php

namespace App\Mail;

use App\Models\ClassroomGrade;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClassroomRetakeDetailsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ClassroomGrade $grade,
        public User $professor,
        public string $details
    ) {}

    public function build(): self
    {
        $subject = $this->grade->classroom?->subject ?? 'Materie';

        return $this
            ->subject('Detalii restanta - ' . $subject)
            ->view('emails.classroom-retake-details')
            ->with([
                'grade' => $this->grade,
                'professor' => $this->professor,
                'details' => $this->details,
            ]);
    }
}

