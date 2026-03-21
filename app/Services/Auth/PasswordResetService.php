<?php

namespace App\Services\Auth;

use App\Mail\PasswordResetLinkMail;
use App\Models\User;
use App\Services\Security\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetService
{
    public const TOKEN_EXPIRATION_MINUTES = 60;

    public function issueToken(User $user): string
    {
        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => strtolower($user->email)],
            [
                'token' => hash('sha256', $token),
                'created_at' => now(),
            ]
        );

        return $token;
    }

    public function sendResetLink(User $targetUser, ?User $actor = null): void
    {
        $token = $this->issueToken($targetUser);
        $url = $this->buildResetUrl($targetUser, $token);

        Mail::to($targetUser->email)->send(
            new PasswordResetLinkMail($targetUser, $url, self::TOKEN_EXPIRATION_MINUTES)
        );

        AuditLogger::log(
            'auth.password_reset_link_sent',
            $actor ?? $targetUser,
            'user',
            $targetUser->id,
            ['initiator_user_id' => $actor?->id]
        );
    }

    public function buildResetUrl(User $user, string $token): string
    {
        return route('password.reset', [
            'token' => $token,
            'email' => strtolower($user->email),
        ]);
    }
}
