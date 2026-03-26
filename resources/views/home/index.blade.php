@extends('layouts.app')

@push('head')
<style>
    .home-grid .card {
        background: linear-gradient(180deg, #fff8ee 0%, #fff2e3 100%);
        border-color: rgba(234, 88, 12, 0.2);
    }
    body[data-theme="dark"] .home-grid .card {
        background: linear-gradient(180deg, #1f2a3d 0%, #182234 100%);
        border-color: #334155;
    }
    .home-highlight {
        background: linear-gradient(125deg, #ffe9cf 0%, #ffd8b5 55%, #fdba74 100%) !important;
        border-color: rgba(234, 88, 12, 0.34) !important;
    }
    body[data-theme="dark"] .home-highlight {
        background: linear-gradient(125deg, #0f172a 0%, #1e293b 55%, #334155 100%) !important;
        border-color: #475569 !important;
    }
    .home-focus {
        background: linear-gradient(145deg, #7c2d12 0%, #9a3412 100%) !important;
        color: #ffedd5;
        border-color: rgba(255, 237, 213, 0.25) !important;
    }
    body[data-theme="dark"] .home-focus {
        background: linear-gradient(145deg, #0b1328 0%, #1e293b 100%) !important;
        color: #dbeafe;
        border-color: #475569 !important;
    }
    .dashboard-cards {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-top: 14px;
    }
    .dashboard-card {
        border: 1px solid rgba(249, 115, 22, 0.24);
        border-radius: 16px;
        padding: 14px 16px;
        background: rgba(255, 255, 255, 0.4);
    }
    body[data-theme="dark"] .dashboard-card {
        border-color: #475569;
        background: rgba(15, 23, 42, 0.55);
    }
    .classroom-actions {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-top: 12px;
    }
    .classroom-action {
        border-radius: 14px;
        border: 1px solid rgba(249, 115, 22, 0.26);
        background: rgba(255, 250, 242, 0.55);
        padding: 14px;
    }
    body[data-theme="dark"] .classroom-action {
        border-color: #475569;
        background: rgba(15, 23, 42, 0.55);
    }
    .metric-value {
        font-size: 28px;
        font-weight: 800;
        margin-top: 4px;
    }
    @media (max-width: 900px) {
        .dashboard-cards,
        .classroom-actions {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
    <section class="hero">
        <div class="pill">{{ __('ui.home.teacher.pill') }}</div>
        <h1>{{ $subtitle }}</h1>
        <p>{{ __('ui.home.teacher.hero_description') }}</p>
    </section>

    <section class="grid home-grid">
        <div class="card span-12 home-highlight">
            <h3>{{ __('ui.home.teacher.quick_actions.title') }}</h3>
            <p class="muted">{{ __('ui.home.teacher.quick_actions.description') }}</p>
            <div class="classroom-actions">
                @foreach($quick_actions as $a)
                    <div class="classroom-action">
                        <strong>{{ $a['label'] }}</strong>
                        <div style="margin-top:10px;">
                            <a class="btn btn-primary" href="{{ $a['href'] }}">{{ __('ui.home.teacher.quick_actions.open') }}</a>
                        </div>
                    </div>
                @endforeach
                @if(auth()->user()?->isAdmin())
                    <div class="classroom-action">
                        <strong>{{ __('ui.home.teacher.quick_actions.manage_users') }}</strong>
                        <div style="margin-top:10px;">
                            <a class="btn btn-secondary" href="{{ route('settings.index') }}">{{ __('ui.home.teacher.quick_actions.manage_accounts') }}</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card span-8">
            <h3>{{ __('ui.home.teacher.monitoring.title') }}</h3>
            <p class="muted">{{ __('ui.home.teacher.monitoring.description') }}</p>
            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <div class="muted">{{ __('ui.home.teacher.monitoring.projects_label') }}</div>
                    <div class="metric-value">{{ $stats['projects'] }}</div>
                    <div class="muted">{{ __('ui.home.teacher.monitoring.projects_suffix') }}</div>
                </div>
                <div class="dashboard-card">
                    <div class="muted">{{ __('ui.home.teacher.monitoring.teams_label') }}</div>
                    <div class="metric-value">{{ $stats['teams'] }}</div>
                    <div class="muted">{{ __('ui.home.teacher.monitoring.teams_suffix') }}</div>
                </div>
                <div class="dashboard-card">
                    <div class="muted">{{ __('ui.home.teacher.monitoring.deliverables_label') }}</div>
                    <div class="metric-value">{{ $stats['deliverables'] }}</div>
                    <div class="muted">{{ __('ui.home.teacher.monitoring.deliverables_suffix') }}</div>
                </div>
            </div>
        </div>

        <div class="card span-4">
            <h3>{{ __('ui.home.teacher.account.title') }}</h3>
            <p class="muted">{!! __('ui.home.teacher.account.role_text', ['role' => '<strong>' . __('ui.home.teacher.account.role_professor') . '</strong>']) !!}</p>
            <div class="notice" style="margin-top:10px;">
                {{ __('ui.home.teacher.account.notice') }}
            </div>
            <p class="muted" style="margin-top:8px;">
                {{ __('ui.home.teacher.account.controlled_accounts') }}
            </p>
            <p class="muted">{{ __('ui.home.teacher.account.support_text') }}</p>
        </div>

        <div class="card span-7">
            <h3>{{ __('ui.home.teacher.recent_projects.title') }}</h3>
            @if(empty($recent_projects))
                <div class="notice">{{ __('ui.home.teacher.recent_projects.empty') }}</div>
            @else
                <table class="table">
                    <thead>
                    <tr>
                        <th>{{ __('ui.home.common.columns.project') }}</th>
                        <th>{{ __('ui.home.common.columns.subject') }}</th>
                        <th>{{ __('ui.home.common.columns.status') }}</th>
                        <th>{{ __('ui.home.common.columns.deadline') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($recent_projects as $project)
                        <tr>
                            <td>{{ $project['title'] }}</td>
                            <td>{{ $project['subject'] ?: '-' }}</td>
                            <td>{{ $project['status'] }}</td>
                            <td>{{ $project['deadline_at'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card span-5 home-focus">
            <h3 style="margin-top:0;">{{ __('ui.home.teacher.priorities_title') }}</h3>
            <ul style="margin:8px 0 0;padding-left:18px;">
                @foreach($announcements as $msg)
                    <li>{{ $msg }}</li>
                @endforeach
            </ul>
        </div>
    </section>
@endsection
