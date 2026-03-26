@php
    $initialTheme = auth()->check()
        ? (auth()->user()->theme_preference ?? 'light')
        : ((string) request()->cookie('upm_theme', 'light') === 'dark' ? 'dark' : 'light');
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'UniProjectManager' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #fff8f1;
            --ink: #2b1608;
            --muted: #7c5e46;
            --accent: #fb923c;
            --accent-strong: #ea580c;
            --accent-soft: #ffedd5;
            --card: #fffdf9;
            --line: #f0d7c2;
            --shadow: 0 16px 30px rgba(120, 53, 15, 0.12);
            --header-bg: rgba(255, 248, 241, 0.85);
            --footer-bg: rgba(255, 248, 241, 0.9);
            --sidebar-bg: rgba(255, 245, 235, 0.92);
        }
        body[data-theme="dark"] {
            --bg: #111827;
            --ink: #e5e7eb;
            --muted: #cbd5e1;
            --accent: #f59e0b;
            --accent-strong: #d97706;
            --accent-soft: #1f2937;
            --card: #1f2937;
            --line: #334155;
            --shadow: 0 16px 30px rgba(2, 6, 23, 0.45);
            --header-bg: rgba(15, 23, 42, 0.92);
            --footer-bg: rgba(15, 23, 42, 0.92);
            --sidebar-bg: rgba(15, 23, 42, 0.96);
        }
        * { box-sizing: border-box; }
        html {
            font-size: 16px;
            overflow-x: hidden;
            width: 100%;
        }
        body {
            margin: 0;
            font-family: "Manrope", "Segoe UI", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(900px 420px at 10% -10%, #ffe8cf 0%, transparent 60%),
                radial-gradient(700px 340px at 90% 0%, #ffd9b5 0%, transparent 55%),
                var(--bg);
            min-height: 100vh;
            overflow-x: hidden;
            width: 100%;
        }
        h1, h2, h3, .brand {
            font-family: "Outfit", "Manrope", sans-serif;
            letter-spacing: -0.01em;
        }
        a { color: inherit; text-decoration: none; }
        .wrap {
            width: 100%;
            max-width: none;
            padding-inline: clamp(14px, 3vw, 36px);
            margin: 0 auto;
        }
        .hero {
            padding: 30px 0 16px;
        }
        .hero h1 {
            margin: 0;
            font-size: clamp(24px, 3vw, 34px);
            letter-spacing: 0.2px;
        }
        .hero p { margin: 10px 0 0; color: var(--muted); max-width: 720px; }
        .grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 18px;
            padding-bottom: 40px;
            width: 100%;
        }
        .card {
            background: var(--card);
            border-radius: 18px;
            padding: 18px 20px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(249, 115, 22, 0.16);
            animation: lift 0.5s ease both;
            min-width: 0;
            overflow-x: auto;
            width: 100%;
        }
        .card h3 { margin: 0 0 10px; }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            background: #fff0df;
            color: #9a3412;
            font-weight: 700;
            font-size: 13px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 12px;
            font-weight: 700;
            border: 0;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            font: inherit;
        }
        .btn-primary { background: linear-gradient(120deg, var(--accent), var(--accent-strong)); color: #fff; }
        .btn-secondary { background: var(--accent-soft); color: #9a3412; border: 1px solid rgba(251, 146, 60, 0.35); }
        .btn-outline { background: #ffffff; color: #9a3412; border: 1px solid rgba(251, 146, 60, 0.45); }
        body[data-theme="dark"] .btn-outline { background: #0f172a; color: #f8fafc; border-color: #475569; }
        .btn-sm { padding: 8px 12px; border-radius: 10px; font-size: 13px; }
        .btn-danger { background: #dc2626; color: #fff; }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 8px 16px rgba(234, 88, 12, 0.2); }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            min-width: 0;
        }
        .table th, .table td { padding: 10px 8px; border-bottom: 1px solid var(--line); text-align: left; }
        .table th { color: var(--muted); font-weight: 700; font-size: 12px; text-transform: uppercase; }
        .notice { padding: 12px 14px; border-radius: 12px; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
        .success { background: #ecfdf5; border: 1px solid #6ee7b7; color: #065f46; }
        .error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        body[data-theme="dark"] .notice { background: #1e293b; border: 1px solid #334155; color: #e2e8f0; }
        body[data-theme="dark"] .success { background: #052e2b; border: 1px solid #0f766e; color: #ccfbf1; }
        body[data-theme="dark"] .error { background: #3f1d1d; border: 1px solid #7f1d1d; color: #fecaca; }
        .muted { color: var(--muted); font-size: 13px; }
        .input {
            width: 100%;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--ink);
            font: inherit;
        }
        body[data-theme="dark"] .input {
            background: #0f172a;
            border-color: #334155;
            color: var(--ink);
        }
        .label {
            display: block;
            color: var(--muted);
            font-size: 13px;
            margin-bottom: 6px;
            font-weight: 600;
        }
        .span-4 { grid-column: span 4; }
        .span-5 { grid-column: span 5; }
        .span-6 { grid-column: span 6; }
        .span-7 { grid-column: span 7; }
        .span-8 { grid-column: span 8; }
        .span-12 { grid-column: span 12; }

        .auth-layout {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            min-height: 100vh;
        }
        .sidebar {
            padding: 12px;
        }
        .sidebar-panel {
            position: sticky;
            top: 12px;
            height: calc(100vh - 24px);
            overflow-y: auto;
            background: var(--sidebar-bg);
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: var(--shadow);
            padding: 16px 12px;
            backdrop-filter: blur(10px);
        }
        .sidebar-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            margin: 0 0 10px;
            padding: 0 6px;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 800;
            font-size: 20px;
            margin-bottom: 16px;
        }
        .brand-logo {
            width: 26px;
            height: 26px;
            border-radius: 7px;
            border: 1px solid var(--line);
        }
        .sidebar-nav {
            display: grid;
            gap: 6px;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border-radius: 12px;
            color: var(--muted);
            font-weight: 700;
            border: 1px solid transparent;
        }
        .sidebar-link:hover {
            color: var(--ink);
            border-color: rgba(249, 115, 22, 0.24);
            background: rgba(249, 115, 22, 0.13);
        }
        .sidebar-link.active {
            color: #9a3412;
            border-color: rgba(234, 88, 12, 0.35);
            background: #fff1e0;
        }
        body[data-theme="dark"] .sidebar-link.active {
            color: #f8fafc;
            background: #1e293b;
            border-color: #475569;
        }
        .sidebar-user {
            margin-top: 16px;
            padding-top: 14px;
            border-top: 1px solid var(--line);
            display: grid;
            gap: 8px;
        }
        .sidebar-user-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            object-fit: cover;
            border: 1px solid rgba(249, 115, 22, 0.35);
        }
        .user-avatar-fallback {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            border: 1px solid rgba(249, 115, 22, 0.35);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #9a3412;
            background: rgba(255, 255, 255, 0.75);
            font-size: 13px;
        }
        body[data-theme="dark"] .user-avatar-fallback {
            color: #e2e8f0;
            border-color: #475569;
            background: rgba(15, 23, 42, 0.6);
        }
        .auth-main {
            min-width: 0;
            display: flex;
            flex-direction: column;
        }
        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 8px;
            padding: 12px clamp(14px, 3vw, 36px);
            border-bottom: 1px solid var(--line);
            backdrop-filter: blur(8px);
            background: var(--header-bg);
        }
        .page {
            flex: 1;
            min-width: 0;
        }
        .notification-menu {
            position: relative;
        }
        .notification-menu summary {
            list-style: none;
        }
        .notification-menu summary::-webkit-details-marker {
            display: none;
        }
        .notification-button {
            position: relative;
        }
        .notification-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            min-width: 18px;
            height: 18px;
            border-radius: 999px;
            background: #dc2626;
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 5px;
            line-height: 1;
            border: 1px solid rgba(255, 255, 255, 0.8);
        }
        .notification-panel {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: min(380px, 92vw);
            max-height: 420px;
            overflow: auto;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: var(--card);
            box-shadow: var(--shadow);
            padding: 10px;
            z-index: 40;
        }
        .notification-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            padding: 4px 4px 8px;
            border-bottom: 1px solid var(--line);
            margin-bottom: 8px;
        }
        .notification-list {
            display: grid;
            gap: 6px;
        }
        .notification-item-form {
            margin: 0;
        }
        .notification-item {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: transparent;
            color: inherit;
            padding: 10px;
            text-align: left;
            cursor: pointer;
            font: inherit;
        }
        .notification-item:hover {
            border-color: rgba(249, 115, 22, 0.32);
            background: rgba(249, 115, 22, 0.08);
        }
        .notification-item.is-unread {
            border-color: rgba(234, 88, 12, 0.42);
            background: rgba(251, 146, 60, 0.12);
        }
        .notification-item-title {
            font-weight: 700;
            margin-bottom: 3px;
        }
        .notification-item-body {
            color: var(--muted);
            font-size: 12px;
            margin-bottom: 4px;
        }
        .notification-item-time {
            color: var(--muted);
            font-size: 11px;
        }

        .guest-header {
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(8px);
            background: var(--header-bg);
            border-bottom: 1px solid var(--line);
        }
        .guest-header .wrap {
            padding-inline: clamp(28px, 6vw, 96px);
        }
        .guest-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 0;
        }
        .guest-main {
            min-height: calc(100vh - 138px);
        }

        .footer {
            border-top: 1px solid var(--line);
            padding: 16px 0 24px;
            background: var(--footer-bg);
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 12px;
        }
        .footer-right {
            text-align: right;
        }
        .footer-logo-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 800;
            letter-spacing: 0.2px;
        }
        .footer-logo {
            width: 24px;
            height: 24px;
            border-radius: 7px;
            border: 1px solid var(--line);
            background: #fff;
            flex-shrink: 0;
        }
        body[data-theme="dark"] .footer-logo {
            background: #0f172a;
        }
        @keyframes lift {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 1100px) {
            .auth-layout {
                grid-template-columns: 230px minmax(0, 1fr);
            }
        }
        @media (max-width: 980px) {
            .auth-layout {
                grid-template-columns: 1fr;
            }
            .sidebar {
                border-bottom: 1px solid var(--line);
            }
            .sidebar-panel {
                position: static;
                height: auto;
                border-radius: 14px;
            }
            .sidebar-nav {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .topbar {
                justify-content: flex-start;
                flex-wrap: wrap;
            }
        }
        @media (max-width: 900px) {
            .span-4, .span-5, .span-6, .span-7, .span-8 { grid-column: span 12; }
            [style*="grid-template-columns:repeat(2,1fr)"],
            [style*="grid-template-columns:repeat(3,1fr)"],
            [style*="grid-template-columns:repeat(4,1fr)"],
            [style*="grid-template-columns: repeat(2, 1fr)"],
            [style*="grid-template-columns: repeat(3, 1fr)"],
            [style*="grid-template-columns: repeat(4, 1fr)"] {
                grid-template-columns: 1fr !important;
            }
        }
        @media (max-width: 640px) {
            .wrap { padding-inline: 12px; }
            .guest-header .wrap { padding-inline: 16px; }
            .sidebar-nav {
                grid-template-columns: 1fr;
            }
            .topbar {
                padding: 10px 12px;
            }
            .table { display: block; overflow-x: auto; white-space: nowrap; }
            .footer-grid {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .footer-right {
                text-align: center;
            }
        }
    </style>
    @stack('head')
</head>
<body data-theme="{{ $initialTheme }}">
@auth
    @php
        $authUser = auth()->user();
        $notifications = collect();
        $unreadNotificationsCount = 0;

        if ($authUser && \Illuminate\Support\Facades\Schema::hasTable('user_notifications')) {
            $notifications = $authUser->userNotifications()->latest()->limit(12)->get();
            $unreadNotificationsCount = $authUser->userNotifications()->whereNull('read_at')->count();
        }
    @endphp
    <div class="auth-layout">
        <aside class="sidebar">
            <div class="sidebar-panel">
                <a class="brand" href="{{ route('landing') }}">
                    <img class="brand-logo" src="{{ asset('logo.png') }}" alt="UniProjectManager logo">
                    UniProjectManager
                </a>

                <p class="sidebar-title">{{ __('ui.layout.main_menu') }}</p>

                <nav class="sidebar-nav" aria-label="{{ __('ui.layout.main_menu') }}">
                    <a class="sidebar-link {{ request()->routeIs('dashboard') || request()->routeIs('home') ? 'active' : '' }}" href="{{ route('dashboard') }}">{{ __('ui.nav.dashboard') }}</a>
                    <a class="sidebar-link {{ request()->routeIs('classrooms.*') ? 'active' : '' }}" href="{{ route('classrooms.index') }}">{{ __('ui.nav.classrooms') }}</a>
                    <a class="sidebar-link {{ request()->routeIs('projects.*') ? 'active' : '' }}" href="{{ route('projects.index') }}">{{ __('ui.nav.projects') }}</a>
                    <a class="sidebar-link {{ request()->routeIs('teams.*') ? 'active' : '' }}" href="{{ route('teams.index') }}">{{ __('ui.nav.teams') }}</a>
                    <a class="sidebar-link {{ request()->routeIs('deliverables.*') ? 'active' : '' }}" href="{{ route('deliverables.index') }}">{{ __('ui.nav.deliverables') }}</a>
                    <a class="sidebar-link {{ request()->routeIs('catalog.*') ? 'active' : '' }}" href="{{ route('catalog.index') }}">{{ __('ui.nav.catalog') }}</a>
                    <a class="sidebar-link {{ request()->routeIs('milestones.*') ? 'active' : '' }}" href="{{ route('milestones.index') }}">{{ __('ui.nav.milestones') }}</a>
                    <a class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">{{ __('ui.nav.profile') }}</a>
                    @if($authUser?->isAdmin())
                        <a class="sidebar-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}">{{ __('ui.nav.admin') }}</a>
                    @endif
                </nav>

                <div class="sidebar-user">
                    <div class="sidebar-user-row">
                        @if(!empty($authUser?->avatar_url))
                            <img src="{{ $authUser->avatar_url }}" alt="{{ $authUser->name }}" class="user-avatar">
                        @else
                            <span class="user-avatar-fallback">
                                {{ strtoupper(substr($authUser?->first_name ?? $authUser?->name ?? 'U', 0, 1)) }}
                            </span>
                        @endif
                        <div>
                            <div style="font-weight:700;">{{ $authUser?->name }}</div>
                            <div class="muted">{{ ucfirst((string) $authUser?->role) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="auth-main">
            <header class="topbar">
                <details class="notification-menu">
                    <summary class="btn btn-outline btn-sm notification-button" aria-label="{{ __('ui.layout.notifications') }}">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 3a5 5 0 0 0-5 5v2.7c0 .8-.3 1.6-.8 2.2l-1.2 1.6A1 1 0 0 0 5.8 16h12.4a1 1 0 0 0 .8-1.5l-1.2-1.6c-.5-.6-.8-1.4-.8-2.2V8a5 5 0 0 0-5-5Z" stroke="currentColor" stroke-width="1.6"/>
                            <path d="M9.5 18a2.5 2.5 0 0 0 5 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                        </svg>
                        {{ __('ui.layout.notifications') }}
                        @if($unreadNotificationsCount > 0)
                            <span class="notification-badge">{{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}</span>
                        @endif
                    </summary>
                    <div class="notification-panel">
                        <div class="notification-head">
                            <strong>{{ __('ui.layout.notifications') }}</strong>
                            @if($unreadNotificationsCount > 0)
                                <form method="POST" action="{{ route('notifications.read-all') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary btn-sm">{{ __('ui.layout.mark_all_notifications') }}</button>
                                </form>
                            @endif
                        </div>
                        @if($notifications->isEmpty())
                            <div class="notice">{{ __('ui.layout.no_notifications') }}</div>
                        @else
                            <div class="notification-list">
                                @foreach($notifications as $notification)
                                    <form class="notification-item-form" method="POST" action="{{ route('notifications.read', $notification) }}">
                                        @csrf
                                        <button type="submit" class="notification-item {{ $notification->read_at ? 'is-read' : 'is-unread' }}">
                                            <div class="notification-item-title">{{ $notification->title }}</div>
                                            @if(!empty($notification->body))
                                                <div class="notification-item-body">{{ $notification->body }}</div>
                                            @endif
                                            <div class="notification-item-time">
                                                {{ optional($notification->created_at)->format('d.m.Y H:i') }}
                                                @if($notification->read_at)
                                                    - {{ __('ui.layout.notification_read') }}
                                                @endif
                                            </div>
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </details>

                <form method="POST" action="{{ route('profile.theme.toggle') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm">
                        {{ ($authUser?->theme_preference ?? 'light') === 'dark' ? __('ui.layout.light_mode') : __('ui.layout.dark_mode') }}
                    </button>
                </form>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">{{ __('ui.layout.logout') }}</button>
                </form>
            </header>

            <main class="wrap page">
                @yield('content')
            </main>

            <footer class="footer">
                <div class="wrap muted footer-grid">
                    <span>{{ __('ui.layout.footer_tagline') }}</span>
                    <span class="footer-logo-wrap">
                        <img src="{{ asset('logo.png') }}" alt="UniProjectManager logo" class="footer-logo">
                        UniProjectManager
                    </span>
                    <span class="footer-right">{{ __('ui.layout.support') }}: uniprojectmanager.noreply@gmail.com</span>
                </div>
            </footer>
        </div>
    </div>
@else
    <header class="guest-header">
        <div class="wrap guest-nav">
            <a class="brand" href="{{ route('landing') }}">
                <img class="brand-logo" src="{{ asset('logo.png') }}" alt="UniProjectManager logo">
                UniProjectManager
            </a>
            <div style="display:flex;gap:8px;align-items:center;">
                <button type="button" class="btn btn-secondary btn-sm" id="guest-theme-toggle">
                    {{ $initialTheme === 'dark' ? __('ui.layout.light_mode') : __('ui.layout.dark_mode') }}
                </button>
                <a class="btn btn-outline btn-sm" href="{{ route('login') }}">{{ __('ui.layout.login') }}</a>
            </div>
        </div>
    </header>

    <main class="wrap page guest-main">
        @yield('content')
    </main>

    <footer class="footer">
        <div class="wrap muted footer-grid">
            <span>{{ __('ui.layout.footer_tagline') }}</span>
            <span class="footer-logo-wrap">
                <img src="{{ asset('logo.png') }}" alt="UniProjectManager logo" class="footer-logo">
                UniProjectManager
            </span>
            <span class="footer-right">{{ __('ui.layout.support') }}: uniprojectmanager.noreply@gmail.com</span>
        </div>
    </footer>
@endauth

<script>
    var showLabel = @json(__('ui.layout.show'));
    var hideLabel = @json(__('ui.layout.hide'));

    document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = btn.getAttribute('data-toggle-password');
            var input = document.getElementById(targetId);
            if (!input) return;
            var isPassword = input.getAttribute('type') === 'password';
            input.setAttribute('type', isPassword ? 'text' : 'password');
            btn.textContent = isPassword ? hideLabel : showLabel;
        });
    });

    (function () {
        var body = document.body;
        if (!body) return;

        function applyTheme(theme) {
            body.setAttribute('data-theme', theme);
            var secureSuffix = window.location.protocol === 'https:' ? '; Secure' : '';
            document.cookie = 'upm_theme=' + theme + '; path=/; max-age=31536000; SameSite=Lax' + secureSuffix;
        }

        var currentTheme = body.getAttribute('data-theme') || 'light';
        applyTheme(currentTheme);

        var guestToggle = document.getElementById('guest-theme-toggle');
        if (!guestToggle) return;
        var lightModeLabel = @json(__('ui.layout.light_mode'));
        var darkModeLabel = @json(__('ui.layout.dark_mode'));

        function syncGuestLabel(theme) {
            guestToggle.textContent = theme === 'dark' ? lightModeLabel : darkModeLabel;
        }

        syncGuestLabel(currentTheme);

        guestToggle.addEventListener('click', function () {
            currentTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(currentTheme);
            syncGuestLabel(currentTheme);
        });
    })();
</script>

</body>
</html>
