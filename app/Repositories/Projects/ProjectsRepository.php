<?php

declare(strict_types=1);

namespace App\Repositories\Projects;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class ProjectsRepository
{
    public function tableExists(): bool
    {
        return Schema::hasTable('projects');
    }

    public function listProjects(int $limit = 20): array
    {
        if (!Schema::hasTable('projects')) {
            return [];
        }

        $columns = $this->availableColumns('projects', [
            'id',
            'code',
            'title',
            'description',
            'domain',
            'status',
            'visibility',
            'min_team_size',
            'max_team_size',
            'start_date',
            'end_date',
            'deadline_at',
            'created_by',
            'created_at',
            'updated_at',
        ]);

        $query = DB::table('projects');
        if (count($columns) > 0) {
            $query->select($columns);
        }

        if (Schema::hasColumn('projects', 'created_at')) {
            $query->orderByDesc('created_at');
        } elseif (Schema::hasColumn('projects', 'id')) {
            $query->orderByDesc('id');
        }

        return $query->limit($limit)->get()->toArray();
    }

    public function create(array $payload): array
    {
        if (!Schema::hasTable('projects')) {
            return ['ok' => false, 'message' => 'Tabela projects nu exista in baza de date.'];
        }

        $columns = $this->availableColumns('projects', array_keys($payload));
        if (count($columns) === 0) {
            return ['ok' => false, 'message' => 'Nu am gasit coloane compatibile in tabela projects.'];
        }

        $data = [];
        foreach ($columns as $column) {
            $data[$column] = $payload[$column] ?? null;
        }

        $projectId = null;

        if (Schema::hasColumn('projects', 'id')) {
            $projectId = (int) DB::table('projects')->insertGetId($data);
        } else {
            DB::table('projects')->insert($data);
        }

        return [
            'ok' => true,
            'message' => 'Proiect salvat cu succes.',
            'project_id' => $projectId,
        ];
    }

    private function availableColumns(string $table, array $candidates): array
    {
        $available = [];
        foreach ($candidates as $column) {
            if (Schema::hasColumn($table, $column)) {
                $available[] = $column;
            }
        }
        return $available;
    }
}
