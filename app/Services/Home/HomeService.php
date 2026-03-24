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
            ['label' => 'Classroom-urile mele', 'href' => '/classrooms'],
            ['label' => 'Proiecte active', 'href' => '/projects'],
            ['label' => 'Echipe', 'href' => '/teams'],
            ['label' => 'Livrabile', 'href' => '/deliverables'],
        ];

        if ($user && $user->hasRole('profesor')) {
            array_unshift($quickActions, ['label' => 'Creeaza un classroom', 'href' => '/classrooms/create']);
        }

        return [
            'title' => 'UniProjectManager',
            'subtitle' => 'Bine ai revenit, ' . $displayName . '.',
            'quick_actions' => $quickActions,
            'stats' => $stats,
            'recent_projects' => $this->normalizeProjects($recentProjects),
            'announcements' => [
                'Planifica proiectele noi pentru clasele active.',
                'Revizuieste echipele si invitatiile in asteptare.',
                'Verifica livrabilele care se apropie de termen.',
            ],
        ];
    }

    public function getStudentHomeData(): array
    {
        $user = Auth::user();
        $recentProjects = $user ? $this->repo->getRecentProjectsForUser($user->id, $user->role) : [];
        $displayName = $user?->first_name ?: $user?->name ?: 'utilizator';

        return [
            'title' => 'Panou student',
            'subtitle' => 'Bine ai revenit, ' . $displayName . '.',
            'highlights' => [
                'Urmareste livrabilele cu termen apropiat.',
                'Ramai conectat la activitatea echipei tale.',
                'Consulta feedback-ul primit la predari.',
            ],
            'recent_projects' => $this->normalizeProjects($recentProjects),
            'actions' => [
                ['label' => 'Classroom-urile mele', 'href' => '/classrooms'],
                ['label' => 'Proiectele mele', 'href' => '/projects'],
                ['label' => 'Echipa mea', 'href' => '/teams'],
                ['label' => 'Predari si livrabile', 'href' => '/deliverables'],
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
