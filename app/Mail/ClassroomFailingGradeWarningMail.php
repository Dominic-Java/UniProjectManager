<?php

namespace App\Mail;

use App\Models\ClassroomGrade;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClassroomFailingGradeWarningMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ClassroomGrade $grade) {}

    public function build(): self
    {
        $subject = $this->grade->classroom?->subject ?? 'Materie';

        return $this
            ->subject('Avertizare restanta - ' . $subject)
            ->view('emails.classroom-failing-grade-warning')
            ->with([
                'grade' => $this->grade,
            ]);
    }
}

