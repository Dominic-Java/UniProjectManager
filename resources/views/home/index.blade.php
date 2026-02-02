@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Dashboard</div>
        <h1>{{ $subtitle }}</h1>
        <p>O vedere de ansamblu asupra proiectelor, echipelor si livrabilelor.</p>
    </section>

    <section class="grid">
        <div class="card span-4">
            <div class="muted">Proiecte</div>
            <div style="font-size:28px;font-weight:800;margin-top:6px;">{{ $stats['projects'] }}</div>
            <div class="muted">Total proiecte inregistrate</div>
        </div>

        <div class="card span-4">
            <div class="muted">Echipe</div>
            <div style="font-size:28px;font-weight:800;margin-top:6px;">{{ $stats['teams'] }}</div>
            <div class="muted">Echipe active/inregistrate</div>
        </div>

        <div class="card span-4">
            <div class="muted">Livrabile</div>
            <div style="font-size:28px;font-weight:800;margin-top:6px;">{{ $stats['deliverables'] }}</div>
            <div class="muted">Fisiere/etape incarcate</div>
        </div>

        <div class="card span-7">
            <h3>Actiuni rapide</h3>
            <div style="display:flex;flex-wrap:wrap;gap:10px;">
                @foreach($quick_actions as $a)
                    <a class="btn btn-primary" href="{{ $a['href'] }}">{{ $a['label'] }}</a>
                @endforeach
                <a class="btn btn-secondary" href="{{ route('settings.index') }}">Setari</a>
            </div>
            <p class="muted">(Link-urile devin active pe masura ce implementam modulele.)</p>
        </div>

        <div class="card span-5">
            <h3>Ghid rapid</h3>
            <ul>
                @foreach($announcements as $msg)
                    <li>{{ $msg }}</li>
                @endforeach
            </ul>
            <p class="muted">Acest dashboard este punctul central al aplicatiei.</p>
        </div>

        <div class="card span-12" style="background:#0f172a;color:#e2e8f0;">
            <strong>Data flow (implementat):</strong>
            <div style="margin-top:8px;">
                <code>/</code> -&gt; <code>routes</code> -&gt; <code>HomeController</code> -&gt; <code>HomeService</code> -&gt; <code>HomeRepository</code> -&gt; <code>DB</code> -&gt; <code>view</code>
            </div>
        </div>
    </section>
@endsection
