<?php

namespace App\Services\Auth;

use App\Mail\AccountWelcomeMail;
use App\Models\User;
use App\Services\Security\AuditLogger;
use Illuminate\Support\Facades\Mail;

class WelcomeMailService
{
    public function __construct(
        private readonly PasswordResetService $passwordResetService
    ) {}

    public function sendWelcomeMail(User $targetUser, ?User $actor = null): bool
    {
        try {
            $token = $this->passwordResetService->issueToken($targetUser);
            $setupUrl = $this->passwordResetService->buildResetUrl($targetUser, $token);

            Mail::to($targetUser->email)->send(
                new AccountWelcomeMail(
                    $targetUser,
                    $actor,
                    $setupUrl,
                    PasswordResetService::TOKEN_EXPIRATION_MINUTES
                )
            );

            AuditLogger::log('users.welcome_mail.sent', $actor ?? $targetUser, 'user', $targetUser->id, [
                'initiator_user_id' => $actor?->id,
                'setup_link_expires_in_minutes' => PasswordResetService::TOKEN_EXPIRATION_MINUTES,
            ]);

            return true;
        } catch (\Throwable $exception) {
            report($exception);

            AuditLogger::log('users.welcome_mail.failed', $actor ?? $targetUser, 'user', $targetUser->id, [
                'initiator_user_id' => $actor?->id,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
