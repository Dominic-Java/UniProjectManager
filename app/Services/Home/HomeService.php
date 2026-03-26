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
        $displayName = $user?->first_name ?: $user?->name ?: __('ui.home.fallback_user');

        $quickActions = [
            ['label' => __('ui.home.teacher.quick_actions.my_classrooms'), 'href' => '/classrooms'],
            ['label' => __('ui.home.teacher.quick_actions.active_projects'), 'href' => '/projects'],
            ['label' => __('ui.home.teacher.quick_actions.teams'), 'href' => '/teams'],
            ['label' => __('ui.home.teacher.quick_actions.deliverables'), 'href' => '/deliverables'],
            ['label' => __('ui.home.teacher.quick_actions.catalog'), 'href' => '/catalog'],
        ];

        if ($user && $user->hasRole('profesor')) {
            array_unshift($quickActions, ['label' => __('ui.home.teacher.quick_actions.create_classroom'), 'href' => '/classrooms/create']);
        }

        return [
            'title' => __('ui.home.teacher.title'),
            'subtitle' => __('ui.home.teacher.subtitle', ['name' => $displayName]),
            'quick_actions' => $quickActions,
            'stats' => $stats,
            'recent_projects' => $this->normalizeProjects($recentProjects),
            'announcements' => [
                __('ui.home.teacher.announcements.plan_projects'),
                __('ui.home.teacher.announcements.review_teams'),
                __('ui.home.teacher.announcements.check_deliverables'),
            ],
        ];
    }

    public function getStudentHomeData(): array
    {
        $user = Auth::user();
        $recentProjects = $user ? $this->repo->getRecentProjectsForUser($user->id, $user->role) : [];
        $courses = $user ? $this->repo->getStudentCourses($user->id) : [];
        $calendarEvents = $user ? $this->repo->getStudentCalendarEvents($user->id) : [];
        $displayName = $user?->first_name ?: $user?->name ?: __('ui.home.fallback_user');

        return [
            'title' => __('ui.home.student.title'),
            'subtitle' => __('ui.home.student.subtitle', ['name' => $displayName]),
            'highlights' => [
                __('ui.home.student.highlights.track_deliverables'),
                __('ui.home.student.highlights.stay_connected'),
                __('ui.home.student.highlights.check_feedback'),
            ],
            'recent_projects' => $this->normalizeProjects($recentProjects),
            'courses' => $this->normalizeCourses($courses),
            'calendar_events' => $this->normalizeCalendarEvents($calendarEvents),
            'actions' => [
                ['label' => __('ui.home.student.actions.my_classrooms'), 'href' => '/classrooms'],
                ['label' => __('ui.home.student.actions.my_projects'), 'href' => '/projects'],
                ['label' => __('ui.home.student.actions.my_team'), 'href' => '/teams'],
                ['label' => __('ui.home.student.actions.submissions_and_deliverables'), 'href' => '/deliverables'],
                ['label' => __('ui.home.student.actions.catalog'), 'href' => '/catalog'],
            ],
        ];
    }

    private function normalizeProjects(array $rows): array
    {
        $normalized = [];

        foreach ($rows as $row) {
            $normalized[] = [
                'id' => $row->id ?? null,
                'title' => $row->title ?? __('ui.home.common.project_default'),
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
                'name' => $row->name ?? __('ui.home.common.classroom_default'),
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
                'tag' => $eventType === 'deliverable_due'
                    ? __('ui.home.common.deliverable_tag')
                    : __('ui.home.common.project_deadline_tag'),
            ];
        }

        return $normalized;
    }
}
