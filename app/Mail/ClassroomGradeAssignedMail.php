<?php

namespace App\Mail;

use App\Models\ClassroomGrade;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClassroomGradeAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ClassroomGrade $grade,
        public User $gradedBy,
        public bool $isUpdate = false
    ) {}

    public function build(): self
    {
        $subject = ($this->isUpdate ? 'Nota actualizata' : 'Nota noua') . ' - ' . ($this->grade->classroom?->subject ?? 'Materie');

        return $this
            ->subject($subject)
            ->view('emails.classroom-grade-assigned')
            ->with([
                'grade' => $this->grade,
                'gradedBy' => $this->gradedBy,
                'isUpdate' => $this->isUpdate,
            ]);
    }
}

