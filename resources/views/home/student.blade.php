@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Student</div>
        <h1>{{ $subtitle }}</h1>
        <p>Acest dashboard este dedicat activitatii tale ca student.</p>
    </section>

    <section class="grid">
        <div class="card span-7">
            <h3>Prioritati</h3>
            <ul>
                @foreach($highlights as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
            <p class="muted">Actualizam aceste informatii cand conectam livrabilele la echipe.</p>
        </div>

        <div class="card span-5">
            <h3>Actiuni rapide</h3>
            <div style="display:flex;flex-wrap:wrap;gap:10px;">
                @foreach($actions as $a)
                    <a class="btn btn-secondary" href="{{ $a['href'] }}">{{ $a['label'] }}</a>
                @endforeach
            </div>
            <p class="muted" style="margin-top:10px;">Ai acces doar la modulele studentesti.</p>
        </div>

        <div class="card span-12" style="background:#f0f9ff;border-color:#bae6fd;">
            <strong>Tip:</strong>
            <span class="muted">Mentine echipa actualizata si urmareste deadline-urile din livrabile.</span>
        </div>
    </section>
@endsection
