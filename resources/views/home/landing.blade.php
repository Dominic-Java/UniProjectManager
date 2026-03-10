@extends('layouts.app')

@push('head')
<style>
    .landing-hero {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 18px;
        padding: 40px 0 20px;
    }
    .landing-panel {
        grid-column: span 7;
        background: linear-gradient(135deg, #ffffff 0%, #f3f8ff 100%);
        border-radius: 24px;
        padding: 26px 28px;
        box-shadow: var(--shadow);
        border: 1px solid rgba(59, 130, 246, 0.2);
    }
    .landing-panel h1 {
        margin: 0 0 10px;
        font-size: clamp(28px, 3.4vw, 40px);
    }
    .landing-panel p { color: var(--muted); }
    .landing-actions { display: flex; gap: 12px; margin-top: 16px; flex-wrap: wrap; }
    .landing-side {
        grid-column: span 5;
        display: grid;
        gap: 12px;
    }
    .landing-side .card { padding: 16px 18px; }
    .feature-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }
    @media (max-width: 900px) {
        .landing-panel, .landing-side { grid-column: span 12; }
        .feature-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
    <section class="landing-hero">
        <div class="landing-panel">
            <div class="pill">UniProjectManager</div>
            <h1>Platforma pentru proiecte studentesti, echipe si livrabile.</h1>
            <p>
                Planifica proiecte, construieste echipe si urmareste progresul. 
                Interfata este diferita pentru studenti si pentru profesori.
            </p>
            <div class="landing-actions">
                <a class="btn btn-primary" href="{{ route('login') }}">Autentificare</a>
                <a class="btn btn-secondary" href="{{ route('register') }}">Creeaza cont</a>
            </div>
        </div>
        <div class="landing-side">
            <div class="card">
                <strong>Structurat pe roluri</strong>
                <p class="muted">Admin, profesor sau student, fiecare are dashboard dedicat.</p>
            </div>
            <div class="card">
                <strong>Deadline-uri clare</strong>
                <p class="muted">Milestones si livrabile organizate in timeline.</p>
            </div>
            <div class="card">
                <strong>Colaborare eficienta</strong>
                <p class="muted">Echipe, invitatii si roluri pe proiect.</p>
            </div>
        </div>
    </section>

    <section class="grid">
        <div class="card span-12">
            <h3>Ce poti face in platforma</h3>
            <div class="feature-grid">
                <div class="card">
                    <strong>Administrare proiecte</strong>
                    <p class="muted">Setezi status, vizibilitate, interval si echipe.</p>
                </div>
                <div class="card">
                    <strong>Management echipe</strong>
                    <p class="muted">Invitatii, roluri, urmarirea contributiilor.</p>
                </div>
                <div class="card">
                    <strong>Livrabile si feedback</strong>
                    <p class="muted">Calificative si comentarii centralizate.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
