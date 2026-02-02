<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'UniProjectManager' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f5f3ee;
            --ink: #1b1f2a;
            --muted: #57606f;
            --accent: #2563eb;
            --accent-2: #10b981;
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
        }
        a { color: inherit; text-decoration: none; }
        .wrap { max-width: 1120px; margin: 0 auto; padding: 0 20px; }
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
        .menu { display: flex; gap: 16px; flex-wrap: wrap; }
        .menu a {
            color: var(--muted);
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 10px;
            transition: all 0.2s ease;
        }
        .menu a:hover { color: var(--ink); background: rgba(37, 99, 235, 0.12); }
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
        .btn-primary { background: var(--accent); color: #fff; }
        .btn-secondary { background: #e2e8f0; color: var(--ink); }
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
        .muted { color: var(--muted); font-size: 13px; }
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
        <div class="brand">UniProjectManager</div>
        <nav class="menu">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('projects.index') }}">Proiecte</a>
            <a href="{{ route('teams.index') }}">Echipe</a>
            <a href="{{ route('deliverables.index') }}">Livrabile</a>
            <a href="{{ route('settings.index') }}">Setari</a>
        </nav>
    </div>
</header>

<div class="wrap">
    @yield('content')
</div>

</body>
</html>
