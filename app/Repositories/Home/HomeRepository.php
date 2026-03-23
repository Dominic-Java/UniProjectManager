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
