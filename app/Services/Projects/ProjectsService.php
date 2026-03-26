<?php

namespace App\Services\Projects;

use App\Models\Project;
use App\Models\User;
use App\Repositories\Projects\ProjectsRepository;
use App\Support\ClassroomAccess;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProjectsService
{
    public function __construct(private ProjectsRepository $repo) {}

    public function getIndexData(?User $user): array
    {
        if (!$this->repo->tableExists() || !$user) {
            return [
                'title' => 'Proiecte',
                'table_exists' => $this->repo->tableExists(),
                'projects' => [],
                'active_projects' => [],
                'archived_projects' => [],
            ];
        }

        $query = Project::query()
            ->with('classroom')
            ->orderByDesc('created_at');

        ClassroomAccess::scopeVisibleProjects($query, $user);
        $projects = $query->get();

        $normalized = $this->normalizeProjects($projects);
        [$activeProjects, $archivedProjects] = $this->splitProjectsByState($normalized);

        return [
            'title' => 'Proiecte',
            'table_exists' => $this->repo->tableExists(),
            'projects' => $normalized,
            'active_projects' => $activeProjects,
            'archived_projects' => $archivedProjects,
        ];
    }

    public function createProject(array $data, ?int $userId): array
    {
        if (!$userId) {
            return ['ok' => false, 'message' => 'Sesiunea nu este valida. Te rugam sa te reconectezi si sa incerci din nou.'];
        }

        $payload = [
            'code' => $data['code'] ?? null,
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? '',
            'domain' => $data['domain'] ?? null,
            'classroom_id' => $data['classroom_id'] ?? null,
            'visibility' => $data['visibility'] ?? 'public',
            'is_retake_project' => (bool) ($data['is_retake_project'] ?? false),
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'deadline_at' => $data['deadline_at'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'min_team_size' => $data['min_team_size'] ?? 1,
            'max_team_size' => $data['max_team_size'] ?? 4,
            'created_by' => $userId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        return $this->repo->create($payload);
    }

    private function normalizeProjects(Collection $rows): array
    {
        $normalized = [];
        foreach ($rows as $row) {
            $title = $row->title ?? null;
            $normalized[] = [
                'id' => $row->id,
                'code' => $row->code,
                'title' => $title ?: 'Proiect',
                'domain' => $row->domain,
                'classroom' => $row->classroom?->name,
                'classroom_code' => $row->classroom?->code,
                'status' => $row->status ?? 'n/a',
                'is_retake_project' => (bool) ($row->is_retake_project ?? false),
                'start_date' => $this->formatDate($row->start_date?->format('Y-m-d')),
                'end_date' => $this->formatDate($row->end_date?->format('Y-m-d')),
                'deadline_at' => $this->formatDateTime($row->deadline_at?->format('Y-m-d H:i:s')),
                'created_at' => $this->formatDateTime($row->created_at?->format('Y-m-d H:i:s')),
            ];
        }
        return $normalized;
    }

    private function splitProjectsByState(array $projects): array
    {
        $active = [];
        $archived = [];

        foreach ($projects as $project) {
            $status = strtolower((string) ($project['status'] ?? ''));

            if (in_array($status, ['closed', 'archived'], true)) {
                $archived[] = $project;
                continue;
            }

            $active[] = $project;
        }

        return [$active, $archived];
    }

    private function formatDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('d.m.Y');
        } catch (\Throwable $e) {
            return $value;
        }
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
