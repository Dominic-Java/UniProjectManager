<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class CloseExpiredProjectsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Schema::hasTable('projects') && Schema::hasColumn('projects', 'deadline_at')) {
            Project::query()
                ->whereNotNull('deadline_at')
                ->where('deadline_at', '<=', now())
                ->whereIn('status', ['draft', 'open', 'in_progress'])
                ->update([
                    'status' => 'closed',
                    'updated_at' => now(),
                ]);

            $retentionHours = max(0, (int) config('uniprojectmanager.expired_project_retention_hours', 24));
            $pruneBefore = $retentionHours > 0 ? now()->subHours($retentionHours) : now();

            Project::query()
                ->whereNotNull('deadline_at')
                ->where('deadline_at', '<=', $pruneBefore)
                ->delete();
        }

        return $next($request);
    }
}
