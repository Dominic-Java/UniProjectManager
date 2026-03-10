<?php

namespace App\Services\Security;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuditLogger
{
    public static function log(string $action, ?User $user = null, ?string $entityType = null, ?int $entityId = null, array $meta = []): void
    {
        DB::table('audit_logs')->insert([
            'user_id' => $user?->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
            'meta' => empty($meta) ? null : json_encode($meta),
            'created_at' => now(),
        ]);
    }
}
