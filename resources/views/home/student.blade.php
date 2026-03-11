@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Student</div>
        <h1>{{ $subtitle }}</h1>
        <p>Tot ce ai nevoie pentru a lucra organizat pe proiecte si livrabile.</p>
    </section>

    <section class="grid">
        <div class="card span-8" style="background:linear-gradient(120deg,#ffffff 0%, #eef6ff 60%, #fef3f2 100%);">
            <h3>Prioritati pe termen scurt</h3>
            <ul>
                @foreach($highlights as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
            <p class="muted">Vei vedea aici deadline-urile si livrabilele imediat ce sunt conectate.</p>
        </div>

        <div class="card span-4">
            <h3>Tip cont</h3>
            <p class="muted">Rolul tau este <strong>Student</strong>.</p>
            <div class="notice" style="margin-top:10px;">
                Domenii publice acceptate: 
                <strong>{{ implode(', ', config('uniprojectmanager.student_domains', [])) ?: 'neconfigurat' }}</strong>
            </div>
            <p class="muted" style="margin-top:8px;">
                Domenii institutionale: <strong>{{ implode(', ', config('uniprojectmanager.institutional_domains', [])) ?: 'neconfigurat' }}</strong>
            </p>
        </div>

        <div class="card span-7">
            <h3>Actiuni rapide</h3>
            <div style="display:flex;flex-wrap:wrap;gap:10px;">
                @foreach($actions as $a)
                    <a class="btn btn-primary" href="{{ $a['href'] }}">{{ $a['label'] }}</a>
                @endforeach
            </div>
            <p class="muted" style="margin-top:10px;">Ai acces doar la modulele studentesti.</p>
        </div>

        <div class="card span-5">
            <h3>Recomandare</h3>
            <p class="muted">Mentine echipa actualizata si urmareste deadline-urile din livrabile.</p>
            <a class="btn btn-secondary" href="{{ route('teams.index') }}">Echipa mea</a>
        </div>

        <div class="card span-12" style="background:#0f172a;color:#e2e8f0;">
            <strong>Flux student:</strong>
            <div style="margin-top:8px;">
                Alatura-te unei echipe &rarr; urmareste milestones &rarr; trimite livrabile &rarr; primeste feedback.
            </div>
        </div>
    </section>
@endsection
