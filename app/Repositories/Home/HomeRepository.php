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
            return DB::table('projects')
                ->select(['id', 'title', 'domain', 'status', 'deadline_at', 'created_at'])
                ->where('created_by', $userId)
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get()
                ->toArray();
        }

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
