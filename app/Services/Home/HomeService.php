<?php

namespace App\Services\Home;

use App\Repositories\Home\HomeRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeService
{
    public function __construct(private HomeRepository $repo) {}

    public function getHomeData(): array
    {
        $stats = $this->repo->getStats();
        $user = Auth::user();
        $recentProjects = $user ? $this->repo->getRecentProjectsForUser($user->id, $user->role) : [];
        $displayName = $user?->first_name ?: $user?->name ?: 'utilizator';

        $quickActions = [
            ['label' => 'Vezi proiecte', 'href' => '/projects'],
            ['label' => 'Echipe', 'href' => '/teams'],
            ['label' => 'Livrabile', 'href' => '/deliverables'],
        ];

        if ($user && $user->hasRole('profesor')) {
            array_unshift($quickActions, ['label' => 'Creeaza proiect', 'href' => '/projects/create']);
        }

        return [
            'title' => 'UniProjectManager',
            'subtitle' => 'Bun venit ' . $displayName . '! Esti gata de un nou proiect?',
            'quick_actions' => $quickActions,
            'stats' => $stats,
            'recent_projects' => $this->normalizeProjects($recentProjects),
            'announcements' => [
                'Adauga primul proiect si defineste etapele.',
                'Creeaza echipe si asociaza studentii.',
                'Incarca livrabile si ofera feedback.',
            ],
        ];
    }

    public function getStudentHomeData(): array
    {
        $user = Auth::user();
        $recentProjects = $user ? $this->repo->getRecentProjectsForUser($user->id, $user->role) : [];
        $displayName = $user?->first_name ?: $user?->name ?: 'utilizator';

        return [
            'title' => 'Student Dashboard',
            'subtitle' => 'Bun venit ' . $displayName . '! Esti gata de un nou proiect?',
            'highlights' => [
                'Urmeaza livrabilele din proiecte.',
                'Colaboreaza cu echipa ta.',
                'Vezi feedbackul profesorilor.',
            ],
            'recent_projects' => $this->normalizeProjects($recentProjects),
            'actions' => [
                ['label' => 'Vezi proiecte', 'href' => '/projects'],
                ['label' => 'Echipele mele', 'href' => '/teams'],
                ['label' => 'Livrabile', 'href' => '/deliverables'],
            ],
        ];
    }

    private function normalizeProjects(array $rows): array
    {
        $normalized = [];

        foreach ($rows as $row) {
            $normalized[] = [
                'id' => $row->id ?? null,
                'title' => $row->title ?? 'Proiect',
                'subject' => $row->domain ?? '-',
                'status' => $row->status ?? '-',
                'deadline_at' => $this->formatDateTime($row->deadline_at ?? null),
            ];
        }

        return $normalized;
    }

    private function formatDateTime($value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('d.m.Y H:i');
        } catch (\Throwable $exception) {
            return (string) $value;
        }
    }
}
