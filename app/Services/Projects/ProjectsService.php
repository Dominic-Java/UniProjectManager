<?php

namespace App\Services\Projects;

use App\Repositories\Projects\ProjectsRepository;
use Carbon\Carbon;

class ProjectsService
{
    public function __construct(private ProjectsRepository $repo) {}

    public function getIndexData(): array
    {
        $projects = $this->repo->listProjects();

        return [
            'title' => 'Proiecte',
            'table_exists' => $this->repo->tableExists(),
            'projects' => $this->normalizeProjects($projects),
        ];
    }

    public function createProject(array $data, ?int $userId): array
    {
        if (!$userId) {
            return ['ok' => false, 'message' => 'Nu am putut identifica utilizatorul curent.'];
        }

        $payload = [
            'code' => $data['code'] ?? null,
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? '',
            'domain' => $data['domain'] ?? null,
            'visibility' => $data['visibility'] ?? 'public',
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'min_team_size' => $data['min_team_size'] ?? 1,
            'max_team_size' => $data['max_team_size'] ?? 4,
            'created_by' => $userId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        return $this->repo->create($payload);
    }

    private function normalizeProjects(array $rows): array
    {
        $normalized = [];
        foreach ($rows as $row) {
            $title = $row->title ?? $row->name ?? null;
            $normalized[] = [
                'id' => $row->id ?? null,
                'code' => $row->code ?? null,
                'title' => $title ?: 'Proiect',
                'status' => $row->status ?? 'n/a',
                'start_date' => $row->start_date ?? null,
                'end_date' => $row->end_date ?? null,
                'created_at' => $this->formatDateTime($row->created_at ?? null),
            ];
        }
        return $normalized;
    }

    private function formatDateTime(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('d.m.Y H:i');
        } catch (\Throwable $e) {
            return $value;
        }
    }
}
