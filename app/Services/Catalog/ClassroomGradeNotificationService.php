<?php

namespace App\Services\Catalog;

use App\Mail\ClassroomFailingGradeWarningMail;
use App\Mail\ClassroomRetakeDetailsMail;
use App\Models\ClassroomGrade;
use App\Models\User;
use App\Services\Security\AuditLogger;
use Illuminate\Support\Facades\Mail;

class ClassroomGradeNotificationService
{
    public function sendFailingWarning(ClassroomGrade $grade, ?User $triggeredBy = null, bool $force = false): bool
    {
        $grade->loadMissing(['student', 'classroom.createdBy', 'gradedBy']);

        if (!$grade->isFailing() || !$grade->student?->email) {
            return false;
        }

        if (!$force && !$this->canSendAutomaticWarning($grade)) {
            return false;
        }

        try {
            Mail::to($grade->student->email)->send(new ClassroomFailingGradeWarningMail($grade));
            $grade->forceFill(['last_warning_sent_at' => now()])->save();

            AuditLogger::log('catalog.warning_mail.sent', $triggeredBy, 'classroom_grade', $grade->id, [
                'student_user_id' => $grade->student_user_id,
                'classroom_id' => $grade->classroom_id,
                'grade_value' => (float) $grade->grade_value,
                'is_forced' => $force,
            ]);

            return true;
        } catch (\Throwable $exception) {
            report($exception);

            AuditLogger::log('catalog.warning_mail.failed', $triggeredBy, 'classroom_grade', $grade->id, [
                'student_user_id' => $grade->student_user_id,
                'classroom_id' => $grade->classroom_id,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    public function sendRetakeDetails(ClassroomGrade $grade, User $professor, string $details): bool
    {
        $grade->loadMissing(['student', 'classroom.createdBy', 'gradedBy']);

        if (!$grade->student?->email || !$grade->isFailing()) {
            return false;
        }

        try {
            Mail::to($grade->student->email)->send(new ClassroomRetakeDetailsMail($grade, $professor, $details));

            AuditLogger::log('catalog.retake_mail.sent', $professor, 'classroom_grade', $grade->id, [
                'student_user_id' => $grade->student_user_id,
                'classroom_id' => $grade->classroom_id,
            ]);

            return true;
        } catch (\Throwable $exception) {
            report($exception);

            AuditLogger::log('catalog.retake_mail.failed', $professor, 'classroom_grade', $grade->id, [
                'student_user_id' => $grade->student_user_id,
                'classroom_id' => $grade->classroom_id,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    public function sendScheduledWarnings(int $limit = 200): int
    {
        $query = ClassroomGrade::query()
            ->with(['student', 'classroom.createdBy', 'gradedBy'])
            ->where('grade_value', '<', 5);

        $cooldownDays = max(0, (int) config('uniprojectmanager.failing_grade_warning_cooldown_days', 7));
        if ($cooldownDays > 0) {
            $cutoff = now()->subDays($cooldownDays);
            $query->where(function ($subQuery) use ($cutoff): void {
                $subQuery
                    ->whereNull('last_warning_sent_at')
                    ->orWhere('last_warning_sent_at', '<=', $cutoff);
            });
        }

        $grades = $query
            ->orderBy('last_warning_sent_at')
            ->limit(max(1, $limit))
            ->get();

        $sent = 0;
        foreach ($grades as $grade) {
            if ($this->sendFailingWarning($grade, null, true)) {
                $sent++;
            }
        }

        return $sent;
    }

    private function canSendAutomaticWarning(ClassroomGrade $grade): bool
    {
        $cooldownDays = max(0, (int) config('uniprojectmanager.failing_grade_warning_cooldown_days', 7));
        if ($cooldownDays === 0) {
            return true;
        }

        if (!$grade->last_warning_sent_at) {
            return true;
        }

        return $grade->last_warning_sent_at->lessThanOrEqualTo(now()->subDays($cooldownDays));
    }
}

