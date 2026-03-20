@extends('layouts.app')

@push('head')
<style>
    .landing-hero {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 18px;
        padding: 44px 0 24px;
        align-items: stretch;
    }
    .landing-panel {
        grid-column: span 7;
        background: linear-gradient(135deg, #ffffff 0%, #eef6ff 55%, #fdf2f8 100%);
        border-radius: 28px;
        padding: 30px 32px;
        box-shadow: var(--shadow);
        border: 1px solid rgba(59, 130, 246, 0.2);
        position: relative;
        overflow: hidden;
    }
    .landing-panel::after {
        content: "";
        position: absolute;
        width: 240px;
        height: 240px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(37, 99, 235, 0.16), transparent 70%);
        top: -80px;
        right: -60px;
    }
    .landing-panel h1 {
        margin: 8px 0 12px;
        font-size: clamp(28px, 3.6vw, 42px);
    }
    .landing-panel p { color: var(--muted); font-size: 15px; }
    .landing-actions { display: flex; gap: 12px; margin-top: 18px; flex-wrap: wrap; }
    .landing-side {
        grid-column: span 5;
        display: grid;
        gap: 12px;
    }
    .landing-side .card { padding: 18px 20px; }
    .metrics {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-top: 18px;
    }
    .metric {
        background: #ffffff;
        border-radius: 16px;
        padding: 12px 14px;
        border: 1px solid rgba(148, 163, 184, 0.25);
    }
    .metric strong { font-size: 18px; display: block; }
    .feature-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }
    .steps {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }
    .cta {
        background: linear-gradient(120deg, #0f172a, #1e293b);
        color: #e2e8f0;
        border-radius: 24px;
        padding: 26px 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
    .cta .btn-secondary {
        background: #e2e8f0;
        color: #0f172a;
    }
    @media (max-width: 900px) {
        .landing-panel, .landing-side { grid-column: span 12; }
        .feature-grid, .metrics, .steps { grid-template-columns: 1fr; }
        .cta { flex-direction: column; align-items: flex-start; }
    }
</style>
@endpush

@section('content')
    <section class="landing-hero">
        <div class="landing-panel">
            <div class="pill">UniProjectManager</div>
            <h1>Platforma pentru proiecte studentesti, coordonate de profesori.</h1>
            <p>
                Centralizeaza proiectele, echipele si livrabilele intr-un singur flux.
                Studentii vad taskurile, profesorii controleaza calendarul si feedback-ul.
            </p>
            <div class="metrics">
                <div class="metric">
                    <strong>+100%</strong>
                    <span class="muted">vizibilitate pe deadline-uri</span>
                </div>
                <div class="metric">
                    <strong>2 roluri</strong>
                    <span class="muted">student / profesor</span>
                </div>
                <div class="metric">
                    <strong>Audit</strong>
                    <span class="muted">actiuni critice inregistrate</span>
                </div>
            </div>
            <div class="landing-actions">
                <a class="btn btn-primary" href="{{ route('login') }}">Autentificare</a>
                <span class="muted" style="align-self:center;">Conturile sunt create de administrator.</span>
            </div>
        </div>
        <div class="landing-side">
            <div class="card">
                <strong>Structurat pe roluri</strong>
                <p class="muted">Profesorii gestioneaza proiectele, studentii lucreaza pe livrabile.</p>
            </div>
            <div class="card">
                <strong>Deadline-uri clare</strong>
                <p class="muted">Milestones si livrabile organizate in timeline.</p>
            </div>
            <div class="card">
                <strong>Colaborare eficienta</strong>
                <p class="muted">Echipe, invitatii si roluri pe proiect.</p>
            </div>
            <div class="card">
                <strong>Siguranta si control</strong>
                <p class="muted">Rate limit, audit log si permisiuni pe rol.</p>
            </div>
        </div>
    </section>

    <section class="grid">
        <div class="card span-12">
            <h3>Ce poti face in platforma</h3>
            <div class="feature-grid">
                <div class="card">
                    <strong>Administrare proiecte</strong>
                    <p class="muted">Setezi status, vizibilitate, interval, echipe si staff.</p>
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
        <div class="card span-12">
            <h3>Flux de lucru in 3 pasi</h3>
            <div class="steps">
                <div class="card">
                    <strong>1. Profesorul creeaza proiectul</strong>
                    <p class="muted">Defineste milestones, livrabile si cerinte.</p>
                </div>
                <div class="card">
                    <strong>2. Studentii formeaza echipe</strong>
                    <p class="muted">Invitatii si roluri pentru colaborare rapida.</p>
                </div>
                <div class="card">
                    <strong>3. Livrabile & evaluare</strong>
                    <p class="muted">Deadline-uri clare si feedback structurat.</p>
                </div>
            </div>
        </div>
        <div class="card span-12">
            <div class="cta">
                <div>
                    <h3 style="margin:0 0 6px;">Vrei un demo pentru facultatea ta?</h3>
                    <p style="margin:0;color:#cbd5f5;">Solicita acces de la administratorul platformei.</p>
                </div>
                <a class="btn btn-secondary" href="{{ route('login') }}">Autentificare</a>
            </div>
        </div>
    </section>
@endsection
