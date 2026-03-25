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
                    'status' => 'archived',
                    'updated_at' => now(),
                ]);
        }

        return $next($request);
    }
}
