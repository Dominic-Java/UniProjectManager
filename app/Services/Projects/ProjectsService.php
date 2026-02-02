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

    public function createProject(array $data): array
    {
        $payload = [
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'status' => $data['status'] ?? 'draft',
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
                'title' => $title ?: 'Proiect',
                'status' => $row->status ?? 'n/a',
                'start_date' => $row->start_date ?? null,
                'end_date' => $row->end_date ?? null,
                'created_at' => $row->created_at ?? null,
            ];
        }
        return $normalized;
    }
}
