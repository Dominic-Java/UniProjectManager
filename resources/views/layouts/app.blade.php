<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'UniProjectManager' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f5f3ee;
            --ink: #101828;
            --muted: #556070;
            --accent: #2563eb;
            --accent-strong: #1d4ed8;
            --accent-soft: #e0ebff;
            --card: #ffffff;
            --line: #e6e2da;
            --shadow: 0 18px 30px rgba(15, 23, 42, 0.08);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Manrope", "Segoe UI", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(900px 420px at 10% -10%, #e3eefc 0%, transparent 60%),
                radial-gradient(700px 340px at 90% 0%, #f8e7f0 0%, transparent 55%),
                var(--bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        a { color: inherit; text-decoration: none; }
        .wrap { max-width: 1120px; margin: 0 auto; padding: 0 20px; }
        main.page { flex: 1; }
        header {
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(8px);
            background: rgba(245, 243, 238, 0.8);
            border-bottom: 1px solid var(--line);
        }
        .nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 0;
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
        .menu a:hover { color: var(--ink); background: rgba(37, 99, 235, 0.12); }
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
            grid-template-columns: repeat(12, 1fr);
            gap: 18px;
            padding-bottom: 40px;
        }
        .card {
            background: var(--card);
            border-radius: 18px;
            padding: 18px 20px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(148, 163, 184, 0.2);
            animation: lift 0.5s ease both;
        }
        .card h3 { margin: 0 0 10px; }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            background: #eef2ff;
            color: #1e3a8a;
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
        .btn-secondary { background: var(--accent-soft); color: #1e3a8a; border: 1px solid rgba(37, 99, 235, 0.18); }
        .btn-outline { background: #ffffff; color: #1e3a8a; border: 1px solid rgba(37, 99, 235, 0.35); }
        .btn-sm { padding: 8px 12px; border-radius: 10px; font-size: 13px; }
        .btn-danger { background: #dc2626; color: #fff; }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 8px 16px rgba(37, 99, 235, 0.18); }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .table th, .table td { padding: 10px 8px; border-bottom: 1px solid var(--line); text-align: left; }
        .table th { color: var(--muted); font-weight: 700; font-size: 12px; text-transform: uppercase; }
        .notice { padding: 12px 14px; border-radius: 12px; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
        .success { background: #ecfdf5; border: 1px solid #6ee7b7; color: #065f46; }
        .error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
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
            background: rgba(245, 243, 238, 0.8);
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
        }
    </style>
    @stack('head')
</head>
<body>
<header>
    <div class="wrap nav">
        <a class="brand" href="{{ route('landing') }}">UniProjectManager</a>
        <nav class="menu">
            @auth
                <a href="{{ route('dashboard') }}">Home</a>
                <a href="{{ route('projects.index') }}">Proiecte</a>
                @if(auth()->user()?->hasRole('profesor'))
                    <a href="{{ route('projects.create') }}">Creeaza proiect</a>
                @endif
                <a href="{{ route('teams.index') }}">Echipe</a>
                <a href="{{ route('deliverables.index') }}">Livrabile</a>
                <a href="{{ route('milestones.index') }}">Milestones</a>
                <a href="{{ route('profile.edit') }}">Profil</a>
                @if(auth()->user()?->isAdmin())
                    <a href="{{ route('settings.index') }}">Setari</a>
                @endif
                <span class="pill">{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
            @endauth
            @guest
                <div class="nav-cta">
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
        <span>Support: admin@uniprojectmanager.test</span>
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
</script>

</body>
</html>
