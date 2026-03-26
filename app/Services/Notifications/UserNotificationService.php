<?php

namespace App\Services\Notifications;

use App\Models\UserNotification;

class UserNotificationService
{
    public function notify(
        int $userId,
        string $title,
        ?string $body = null,
        ?string $url = null,
        ?string $type = null
    ): UserNotification {
        return UserNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'read_at' => null,
        ]);
    }
}

