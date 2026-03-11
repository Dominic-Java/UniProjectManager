@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Profesor</div>
        <h1>{{ $subtitle }}</h1>
        <p>Coordoneaza proiectele studentesti, stabileste livrabilele si urmareste progresul echipelor.</p>
    </section>

    <section class="grid">
        <div class="card span-8" style="background:linear-gradient(120deg,#ffffff 0%, #eef6ff 55%, #f5f3ff 100%);">
            <h3>Stare generala</h3>
            <p class="muted">Ai o vedere rapida asupra proiectelor si livrabilelor active.</p>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:16px;">
                <div class="card" style="padding:14px 16px;">
                    <div class="muted">Proiecte</div>
                    <div style="font-size:26px;font-weight:800;margin-top:6px;">{{ $stats['projects'] }}</div>
                    <div class="muted">Total proiecte</div>
                </div>
                <div class="card" style="padding:14px 16px;">
                    <div class="muted">Echipe</div>
                    <div style="font-size:26px;font-weight:800;margin-top:6px;">{{ $stats['teams'] }}</div>
                    <div class="muted">Echipe inregistrate</div>
                </div>
                <div class="card" style="padding:14px 16px;">
                    <div class="muted">Livrabile</div>
                    <div style="font-size:26px;font-weight:800;margin-top:6px;">{{ $stats['deliverables'] }}</div>
                    <div class="muted">Deadline-uri active</div>
                </div>
            </div>
        </div>

        <div class="card span-4">
            <h3>Tip cont</h3>
            <p class="muted">Rolul tau este <strong>Profesor</strong>.</p>
            <div class="notice" style="margin-top:10px;">
                Domenii publice acceptate: 
                <strong>{{ implode(', ', config('uniprojectmanager.student_domains', [])) ?: 'neconfigurat' }}</strong>
            </div>
            <p class="muted" style="margin-top:8px;">
                Domenii institutionale: <strong>{{ implode(', ', config('uniprojectmanager.institutional_domains', [])) ?: 'neconfigurat' }}</strong>
            </p>
            <p class="muted">Poti permite si prin whitelist de email.</p>
        </div>

        <div class="card span-7">
            <h3>Actiuni rapide</h3>
            <div style="display:flex;flex-wrap:wrap;gap:10px;">
                @foreach($quick_actions as $a)
                    <a class="btn btn-primary" href="{{ $a['href'] }}">{{ $a['label'] }}</a>
                @endforeach
                @if(auth()->user()?->hasRole('profesor'))
                    <a class="btn btn-secondary" href="{{ route('settings.index') }}">Setari utilizatori</a>
                @endif
            </div>
            <p class="muted" style="margin-top:10px;">Recomandat: creeaza proiecte si seteaza milestones.</p>
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
            <strong>Flux recomandat:</strong>
            <div style="margin-top:8px;">
                Configureaza proiecte &rarr; seteaza livrabile &rarr; monitorizeaza echipele &rarr; ofera feedback.
            </div>
        </div>
    </section>
@endsection
