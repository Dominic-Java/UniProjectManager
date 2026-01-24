<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — Home</title>
    <style>
        body { margin:0; font-family: Arial, Helvetica, sans-serif; background:#f4f6f8; color:#111827; }
        header { background:#111827; color:#fff; padding:18px 0; }
        .container { max-width:1100px; margin:0 auto; padding:0 18px; }
        .brand { display:flex; justify-content:space-between; align-items:center; }
        .brand a { color:#9ca3af; text-decoration:none; margin-left:14px; }
        .hero { margin:24px 0 18px; }
        .hero h1 { margin:0; font-size:28px; }
        .hero p { margin:8px 0 0; color:#6b7280; }

        .grid { display:grid; grid-template-columns: repeat(12, 1fr); gap:14px; }
        .card { background:#fff; border-radius:12px; padding:18px; box-shadow: 0 10px 20px rgba(0,0,0,.06); }
        .stat { grid-column: span 4; }
        .stat .num { font-size:28px; font-weight:700; margin:8px 0 0; }
        .stat .label { color:#6b7280; font-size:14px; }

        .actions { grid-column: span 7; }
        .ann { grid-column: span 5; }

        .btns { display:flex; flex-wrap:wrap; gap:10px; margin-top:12px; }
        .btn { display:inline-block; padding:10px 14px; border-radius:10px; text-decoration:none; font-weight:700; }
        .btn-primary { background:#2563eb; color:#fff; }
        .btn-secondary { background:#e5e7eb; color:#111827; }

        ul { margin:10px 0 0; padding-left:18px; color:#374151; }
        .muted { color:#6b7280; font-size:13px; margin-top:10px; }

        .flow { grid-column: span 12; background:#0b1220; color:#cbd5e1; border-radius:12px; padding:14px 18px; }
        code { color:#93c5fd; }

        @media (max-width: 900px) {
            .stat { grid-column: span 12; }
            .actions { grid-column: span 12; }
            .ann { grid-column: span 12; }
        }
    </style>
</head>
<body>

<header>
    <div class="container brand">
        <div><strong>UniProjectManager</strong></div>
        <nav>
            <a href="/home">Home</a>
            <a href="/projects">Proiecte</a>
            <a href="/teams">Echipe</a>
            <a href="/deliverables">Livrabile</a>
        </nav>
    </div>
</header>

<div class="container hero">
    <h1>{{ $subtitle }}</h1>
    <p>O vedere de ansamblu asupra proiectelor, echipelor și livrabilelor.</p>
</div>

<div class="container grid">

    <div class="card stat">
        <div class="label">Proiecte</div>
        <div class="num">{{ $stats['projects'] }}</div>
        <div class="muted">Total proiecte înregistrate</div>
    </div>

    <div class="card stat">
        <div class="label">Echipe</div>
        <div class="num">{{ $stats['teams'] }}</div>
        <div class="muted">Echipe active/înregistrate</div>
    </div>

    <div class="card stat">
        <div class="label">Livrabile</div>
        <div class="num">{{ $stats['deliverables'] }}</div>
        <div class="muted">Fișiere/etape încărcate</div>
    </div>

    <div class="card actions">
        <h3 style="margin:0;">Acțiuni rapide</h3>
        <div class="btns">
            @foreach($quick_actions as $a)
                <a class="btn btn-primary" href="{{ $a['href'] }}">{{ $a['label'] }}</a>
            @endforeach
            <a class="btn btn-secondary" href="/settings">Setări</a>
        </div>
        <p class="muted">
            (Link-urile vor deveni active pe măsură ce implementăm modulele Projects/Teams/Deliverables.)
        </p>
    </div>

    <div class="card ann">
        <h3 style="margin:0;">Ghid rapid</h3>
        <ul>
            @foreach($announcements as $msg)
                <li>{{ $msg }}</li>
            @endforeach
        </ul>
        <p class="muted">Acest dashboard e punctul central al aplicației.</p>
    </div>

    <div class="flow">
        <strong>Data flow (implementat):</strong>
        <div style="margin-top:8px;">
            <code>/home</code> → <code>routes</code> → <code>HomeController</code> → <code>HomeService</code> → <code>HomeRepository</code> → <code>DB</code> → <code>view</code>
        </div>
    </div>

</div>

</body>
</html>
