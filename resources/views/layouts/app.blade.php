@php
    $initialTheme = auth()->check()
        ? (auth()->user()->theme_preference ?? 'light')
        : ((string) request()->cookie('upm_theme', 'light') === 'dark' ? 'dark' : 'light');
@endphp
<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'UniProjectManager' }}</title>
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
            --footer-bg: rgba(255, 248, 241, 0.85);
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
            --header-bg: rgba(15, 23, 42, 0.9);
            --footer-bg: rgba(15, 23, 42, 0.9);
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
            display: flex;
            flex-direction: column;
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
            padding-inline: clamp(14px, 3.2vw, 56px);
            margin: 0 auto;
        }
        main.page { flex: 1; min-width: 0; width: 100%; }
        main.page > section { width: 100%; }
        header {
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(8px);
            background: var(--header-bg);
            border-bottom: 1px solid var(--line);
        }
        .nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 0;
            gap: 26px;
        }
        .nav-shell {
            padding-inline: clamp(28px, 6vw, 128px);
        }
        .brand {
            font-weight: 800;
            letter-spacing: 0.2px;
            font-size: 18px;
        }
        .menu { display: flex; gap: 16px; flex-wrap: wrap; align-items: center; }
        .menu a {
            color: var(--muted);
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 10px;
            transition: all 0.2s ease;
        }
        .menu a:hover { color: var(--ink); background: rgba(249, 115, 22, 0.14); }
        .menu-dropdown {
            position: relative;
        }
        .menu-dropdown-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--muted);
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 10px;
            transition: all 0.2s ease;
            border: 0;
            background: transparent;
            cursor: pointer;
            font: inherit;
        }
        .menu-dropdown-toggle::after {
            content: "▾";
            font-size: 11px;
            opacity: 0.85;
        }
        .menu-dropdown:hover .menu-dropdown-toggle,
        .menu-dropdown:focus-within .menu-dropdown-toggle {
            color: var(--ink);
            background: rgba(249, 115, 22, 0.14);
        }
        .menu-dropdown-panel {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            min-width: 220px;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: var(--card);
            box-shadow: var(--shadow);
            padding: 8px;
            display: grid;
            gap: 4px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(6px);
            transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s ease;
            pointer-events: none;
            z-index: 40;
        }
        .menu-dropdown:hover .menu-dropdown-panel,
        .menu-dropdown:focus-within .menu-dropdown-panel {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            pointer-events: auto;
        }
        .menu-dropdown-panel a {
            color: var(--ink);
            font-weight: 600;
            padding: 9px 10px;
            border-radius: 8px;
        }
        .menu-dropdown-panel a:hover {
            background: rgba(249, 115, 22, 0.14);
        }
        .menu form { margin: 0; }
        .nav-cta { display: inline-flex; gap: 10px; align-items: center; }
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
            font: inherit;
        }
        .label {
            display: block;
            color: var(--muted);
            font-size: 13px;
            margin-bottom: 6px;
            font-weight: 600;
        }
        .footer {
            border-top: 1px solid var(--line);
            padding: 18px 0 26px;
            background: var(--footer-bg);
        }
        .span-4 { grid-column: span 4; }
        .span-5 { grid-column: span 5; }
        .span-6 { grid-column: span 6; }
        .span-7 { grid-column: span 7; }
        .span-8 { grid-column: span 8; }
        .span-12 { grid-column: span 12; }
        @keyframes lift {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 900px) {
            .span-4, .span-5, .span-6, .span-7, .span-8 { grid-column: span 12; }
            .menu { gap: 10px; }
            .table { min-width: 0; }
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
            .nav-shell { padding-inline: 12px; }
            .nav { align-items: flex-start; flex-direction: column; gap: 10px; }
            .menu { width: 100%; }
            .menu-dropdown-panel {
                position: static;
                min-width: 0;
                margin-top: 8px;
                box-shadow: none;
                display: none;
                opacity: 1;
                visibility: visible;
                transform: none;
                pointer-events: auto;
            }
            .menu-dropdown:hover .menu-dropdown-panel,
            .menu-dropdown:focus-within .menu-dropdown-panel {
                display: grid;
            }
            .btn { width: 100%; }
            .hero { padding-top: 20px; }
            .table { display: block; overflow-x: auto; white-space: nowrap; }
        }
    </style>
    @stack('head')
</head>
<body data-theme="{{ $initialTheme }}">
<header>
    <div class="wrap nav nav-shell">
        <a class="brand" href="{{ route('landing') }}">UniProjectManager</a>
        <nav class="menu">
            @auth
                <a href="{{ route('dashboard') }}">Home</a>
                <div class="menu-dropdown">
                    <button type="button" class="menu-dropdown-toggle" aria-haspopup="menu" aria-expanded="false">
                        Classroom
                    </button>
                    <div class="menu-dropdown-panel" role="menu" aria-label="Classroom">
                        <a role="menuitem" href="{{ route('classrooms.index') }}">Classroom-uri</a>
                        @if(auth()->user()?->hasRole('profesor'))
                            <a role="menuitem" href="{{ route('classrooms.create') }}">Creeaza classroom</a>
                        @endif
                    </div>
                </div>
                <a href="{{ route('projects.index') }}">Proiecte</a>
                <a href="{{ route('teams.index') }}">Echipe</a>
                <a href="{{ route('deliverables.index') }}">Livrabile</a>
                <a href="{{ route('milestones.index') }}">Milestones</a>
                <a href="{{ route('profile.edit') }}">Profil</a>
                @if(auth()->user()?->isAdmin())
                    <a href="{{ route('settings.index') }}">Setari</a>
                @endif
                <span class="pill">{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
                <form method="POST" action="{{ route('profile.theme.toggle') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm">
                        {{ (auth()->user()->theme_preference ?? 'light') === 'dark' ? 'Light mode' : 'Dark mode' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
            @endauth
            @guest
                <div class="nav-cta">
                    <button type="button" class="btn btn-secondary btn-sm" id="guest-theme-toggle">
                        {{ $initialTheme === 'dark' ? 'Light mode' : 'Dark mode' }}
                    </button>
                    <a class="btn btn-outline btn-sm" href="{{ route('login') }}">Login</a>
                </div>
            @endguest
        </nav>
    </div>
</header>

<main class="wrap page">
    @yield('content')
</main>

<footer class="footer">
    <div class="wrap muted" style="display:flex;flex-wrap:wrap;gap:10px;justify-content:space-between;">
        <span>UniProjectManager · Platforma pentru proiecte studentesti</span>
        <span>Support: uniprojectmanager.noreply@gmail.com</span>
    </div>
</footer>

<script>
    document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = btn.getAttribute('data-toggle-password');
            var input = document.getElementById(targetId);
            if (!input) return;
            var isPassword = input.getAttribute('type') === 'password';
            input.setAttribute('type', isPassword ? 'text' : 'password');
            btn.textContent = isPassword ? 'Ascunde' : 'Afiseaza';
        });
    });

    (function () {
        var body = document.body;
        if (!body) return;

        function applyTheme(theme) {
            body.setAttribute('data-theme', theme);
            document.cookie = 'upm_theme=' + theme + '; path=/; max-age=31536000; SameSite=Lax';
        }

        var currentTheme = body.getAttribute('data-theme') || 'light';
        applyTheme(currentTheme);

        var guestToggle = document.getElementById('guest-theme-toggle');
        if (!guestToggle) return;

        function syncGuestLabel(theme) {
            guestToggle.textContent = theme === 'dark' ? 'Light mode' : 'Dark mode';
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
