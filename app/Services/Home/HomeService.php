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
        $recentRole = $user && ($user->hasRole('profesor') || $user->isAdmin()) ? 'profesor' : (string) $user?->role;
        $recentProjects = $user ? $this->repo->getRecentProjectsForUser($user->id, $recentRole) : [];
        $displayName = $user?->first_name ?: $user?->name ?: 'utilizator';

        $quickActions = [
            ['label' => 'Classroom-urile mele', 'href' => '/classrooms'],
            ['label' => 'Proiecte active', 'href' => '/projects'],
            ['label' => 'Echipe', 'href' => '/teams'],
            ['label' => 'Livrabile', 'href' => '/deliverables'],
            ['label' => 'Catalog', 'href' => '/catalog'],
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
        $courses = $user ? $this->repo->getStudentCourses($user->id) : [];
        $calendarEvents = $user ? $this->repo->getStudentCalendarEvents($user->id) : [];
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
            'courses' => $this->normalizeCourses($courses),
            'calendar_events' => $this->normalizeCalendarEvents($calendarEvents),
            'actions' => [
                ['label' => 'Classroom-urile mele', 'href' => '/classrooms'],
                ['label' => 'Proiectele mele', 'href' => '/projects'],
                ['label' => 'Echipa mea', 'href' => '/teams'],
                ['label' => 'Predari si livrabile', 'href' => '/deliverables'],
                ['label' => 'Catalog', 'href' => '/catalog'],
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

    private function normalizeCourses(array $rows): array
    {
        $normalized = [];

        foreach ($rows as $row) {
            $fullName = trim(((string) ($row->professor_first_name ?? '')) . ' ' . ((string) ($row->professor_last_name ?? '')));

            $normalized[] = [
                'id' => $row->id ?? null,
                'name' => $row->name ?? 'Classroom',
                'subject' => $row->subject ?? '-',
                'code' => $row->code ?? '-',
                'is_active' => (bool) ($row->is_active ?? true),
                'professor_name' => $fullName !== '' ? $fullName : '-',
            ];
        }

        return $normalized;
    }

    private function normalizeCalendarEvents(array $rows): array
    {
        $normalized = [];

        foreach ($rows as $row) {
            $eventType = (string) ($row->event_type ?? '');
            $normalized[] = [
                'event_type' => $eventType,
                'event_title' => $row->event_title ?? '-',
                'subject' => $row->subject ?? '-',
                'classroom_name' => $row->classroom_name ?? '-',
                'event_at' => $this->formatDateTime($row->event_at ?? null),
                'tag' => $eventType === 'deliverable_due' ? 'Livrabil' : 'Deadline proiect',
            ];
        }

        return $normalized;
    }
}
