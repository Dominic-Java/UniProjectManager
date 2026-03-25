<?php

declare(strict_types=1);

namespace App\Repositories\Home;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class HomeRepository
{
    /**
     * Returneaza statistici de baza pentru pagina Home.
     * Verifica existenta tabelelor pentru a evita erori la prima rulare.
     */
    public function getStats(): array
    {
        return [
            'projects' => $this->countIfExists('projects'),
            'teams' => $this->countIfExists('teams'),
            'deliverables' => $this->countIfExists('deliverables'),
        ];
    }

    public function getRecentProjectsForUser(int $userId, string $role, int $limit = 6): array
    {
        if (!Schema::hasTable('projects')) {
            return [];
        }

        if ($role === 'profesor') {
            if (!Schema::hasTable('classrooms')) {
                return DB::table('projects')
                    ->select(['id', 'title', 'domain', 'status', 'deadline_at', 'created_at'])
                    ->where('created_by', $userId)
                    ->orderByDesc('created_at')
                    ->limit($limit)
                    ->get()
                    ->toArray();
            }

            return DB::table('projects')
                ->leftJoin('classrooms', 'classrooms.id', '=', 'projects.classroom_id')
                ->leftJoin('classroom_members', function ($join) use ($userId): void {
                    $join->on('classroom_members.classroom_id', '=', 'projects.classroom_id')
                        ->where('classroom_members.user_id', '=', $userId)
                        ->where('classroom_members.role', '=', 'teacher');
                })
                ->leftJoin('project_staff', function ($join) use ($userId): void {
                    $join->on('project_staff.project_id', '=', 'projects.id')
                        ->where('project_staff.professor_user_id', '=', $userId);
                })
                ->select([
                    'projects.id',
                    'projects.title',
                    'projects.domain',
                    'projects.status',
                    'projects.deadline_at',
                    'projects.created_at',
                ])
                ->where(function ($query) use ($userId): void {
                    $query->where('classrooms.created_by', $userId)
                        ->orWhereNotNull('classroom_members.user_id')
                        ->orWhereNotNull('project_staff.professor_user_id')
                        ->orWhere(function ($legacyQuery) use ($userId): void {
                            $legacyQuery
                                ->whereNull('projects.classroom_id')
                                ->where('projects.created_by', $userId);
                        });
                })
                ->distinct()
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get()
                ->toArray();
        }

        if (!Schema::hasTable('classroom_members')) {
            if (!Schema::hasTable('teams') || !Schema::hasTable('team_members')) {
                return [];
            }

            return DB::table('projects')
                ->join('teams', 'teams.project_id', '=', 'projects.id')
                ->join('team_members', 'team_members.team_id', '=', 'teams.id')
                ->where('team_members.user_id', $userId)
                ->select([
                    'projects.id',
                    'projects.title',
                    'projects.domain',
                    'projects.status',
                    'projects.deadline_at',
                    'projects.created_at',
                ])
                ->distinct()
                ->orderByDesc('projects.created_at')
                ->limit($limit)
                ->get()
                ->toArray();
        }

        return DB::table('projects')
            ->join('classroom_members', 'classroom_members.classroom_id', '=', 'projects.classroom_id')
            ->where('classroom_members.user_id', $userId)
            ->select([
                'projects.id',
                'projects.title',
                'projects.domain',
                'projects.status',
                'projects.deadline_at',
                'projects.created_at',
            ])
            ->distinct()
            ->orderByDesc('projects.created_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getStudentCourses(int $userId, int $limit = 12): array
    {
        if (!Schema::hasTable('classrooms') || !Schema::hasTable('classroom_members')) {
            return [];
        }

        return DB::table('classroom_members')
            ->join('classrooms', 'classrooms.id', '=', 'classroom_members.classroom_id')
            ->leftJoin('users as professors', 'professors.id', '=', 'classrooms.created_by')
            ->where('classroom_members.user_id', $userId)
            ->where('classroom_members.role', 'student')
            ->select([
                'classrooms.id',
                'classrooms.name',
                'classrooms.subject',
                'classrooms.code',
                'classrooms.is_active',
                'professors.first_name as professor_first_name',
                'professors.last_name as professor_last_name',
            ])
            ->orderBy('classrooms.subject')
            ->orderBy('classrooms.name')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getStudentCalendarEvents(int $userId, int $limit = 20): array
    {
        if (!Schema::hasTable('projects') || !Schema::hasTable('classroom_members') || !Schema::hasTable('classrooms')) {
            return [];
        }

        $events = [];

        if (Schema::hasColumn('projects', 'deadline_at')) {
            $projectEvents = DB::table('projects')
                ->join('classroom_members', 'classroom_members.classroom_id', '=', 'projects.classroom_id')
                ->join('classrooms', 'classrooms.id', '=', 'projects.classroom_id')
                ->where('classroom_members.user_id', $userId)
                ->where('classroom_members.role', 'student')
                ->whereNotNull('projects.deadline_at')
                ->where('projects.deadline_at', '>=', now()->subDays(1))
                ->select([
                    DB::raw("'project_deadline' as event_type"),
                    'projects.id as reference_id',
                    'projects.title as event_title',
                    'classrooms.subject as subject',
                    'classrooms.name as classroom_name',
                    'projects.deadline_at as event_at',
                ])
                ->get()
                ->toArray();

            $events = array_merge($events, $projectEvents);
        }

        if (Schema::hasTable('deliverables') && Schema::hasColumn('deliverables', 'due_at')) {
            $deliverableEvents = DB::table('deliverables')
                ->join('projects', 'projects.id', '=', 'deliverables.project_id')
                ->join('classroom_members', 'classroom_members.classroom_id', '=', 'projects.classroom_id')
                ->join('classrooms', 'classrooms.id', '=', 'projects.classroom_id')
                ->where('classroom_members.user_id', $userId)
                ->where('classroom_members.role', 'student')
                ->whereNotNull('deliverables.due_at')
                ->where('deliverables.due_at', '>=', now()->subDays(1))
                ->select([
                    DB::raw("'deliverable_due' as event_type"),
                    'deliverables.id as reference_id',
                    'deliverables.title as event_title',
                    'classrooms.subject as subject',
                    'classrooms.name as classroom_name',
                    'deliverables.due_at as event_at',
                ])
                ->get()
                ->toArray();

            $events = array_merge($events, $deliverableEvents);
        }

        usort($events, static function ($a, $b): int {
            $left = strtotime((string) ($a->event_at ?? '')) ?: PHP_INT_MAX;
            $right = strtotime((string) ($b->event_at ?? '')) ?: PHP_INT_MAX;
            return $left <=> $right;
        });

        return array_slice($events, 0, max(1, $limit));
    }

    /**
     * Numara inregistrarile dintr-un tabel doar daca acesta exista.
     */
    private function countIfExists(string $table): int
    {
        return Schema::hasTable($table)
            ? (int) DB::table($table)->count()
            : 0;
    }
}
