@extends('layouts.app')

@push('head')
<style>
    .landing-shell {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 18px;
        padding: 38px 0 22px;
    }
    .landing-hero {
        border-radius: 28px;
        padding: 30px;
        background: linear-gradient(135deg, #fffefc 0%, #fff2df 58%, #ffe2c7 100%);
        border: 1px solid rgba(251, 146, 60, 0.28);
        box-shadow: var(--shadow);
        position: relative;
        overflow: hidden;
    }
    body[data-theme="dark"] .landing-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 58%, #334155 100%);
        border-color: #475569;
    }
    .landing-hero::before {
        content: "";
        position: absolute;
        right: -70px;
        top: -70px;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(249, 115, 22, 0.2), transparent 72%);
    }
    .landing-hero h1 {
        margin: 10px 0 12px;
        font-size: clamp(30px, 4vw, 48px);
        line-height: 1.12;
    }
    .landing-hero p {
        margin: 0;
        max-width: 62ch;
        color: var(--muted);
        font-size: 16px;
    }
    .landing-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
    }
    .landing-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
    }
    .landing-tag {
        border-radius: 999px;
        padding: 6px 12px;
        background: #fff7ed;
        border: 1px solid rgba(251, 146, 60, 0.26);
        color: #9a3412;
        font-weight: 700;
        font-size: 13px;
    }
    body[data-theme="dark"] .landing-tag {
        background: #1e293b;
        border-color: #475569;
        color: #f8fafc;
    }
    .landing-side {
        display: grid;
        gap: 12px;
    }
    .landing-side .card {
        padding: 18px;
    }
    body[data-theme="dark"] .landing-side .card {
        background: rgba(15, 23, 42, 0.55);
        border-color: #475569;
    }
    .friendly-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }
    .friendly-grid .card {
        padding: 16px;
    }
    .friendly-step {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }
    .friendly-step .card {
        padding: 16px;
    }
    body[data-theme="dark"] .friendly-grid .card,
    body[data-theme="dark"] .friendly-step .card {
        background: rgba(15, 23, 42, 0.55);
        border-color: #475569;
    }
    .landing-cta {
        border-radius: 22px;
        background: linear-gradient(120deg, #8a320f, #bf4b14);
        color: #ffedd5;
        padding: 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .landing-cta .btn-secondary {
        background: #fff7ed;
        color: #9a3412;
    }
    body[data-theme="dark"] .landing-cta {
        background: linear-gradient(140deg, #0b1328 0%, #1e293b 100%);
        color: #dbeafe;
    }
    body[data-theme="dark"] .landing-cta .btn-secondary {
        background: #1e293b;
        color: #e2e8f0;
        border: 1px solid #475569;
    }
    @media (max-width: 1000px) {
        .landing-shell {
            grid-template-columns: 1fr;
        }
        .friendly-grid,
        .friendly-step {
            grid-template-columns: 1fr;
        }
        .landing-cta {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
@endpush

@section('content')
    <section class="landing-shell">
        <div class="landing-hero">
            <div class="pill">Bine ai venit in UniProjectManager</div>
            <h1>Platforma academica in care profesorii si studentii colaboreaza clar si eficient.</h1>
            <p>
                Proiectele, etapele si livrabilele sunt organizate intr-un flux usor de urmarit.
                Echipele stiu ce au de facut, iar cadrele didactice monitorizeaza progresul in timp real.
            </p>
            <div class="landing-buttons">
                <a class="btn btn-primary" href="{{ route('login') }}">Autentificare</a>
                <span class="muted" style="align-self:center;">Accesul este oferit de administrator.</span>
            </div>
            <div class="landing-tags">
                <span class="landing-tag">Organizare academica</span>
                <span class="landing-tag">Colaborare in echipa</span>
                <span class="landing-tag">Termene clare</span>
            </div>
        </div>

        <div class="landing-side">
            <div class="card">
                <strong>Structura de tip classroom, orientata pe proiecte</strong>
                <p class="muted">Mai putine mesaje dispersate si mai mult timp pentru activitatea didactica.</p>
            </div>
            <div class="card">
                <strong>Claritate pentru studenti</strong>
                <p class="muted">Interfata simpla, cu informatii esentiale si pasi usor de urmat.</p>
            </div>
            <div class="card">
                <strong>Control academic pentru profesori</strong>
                <p class="muted">Roluri bine definite, trasabilitate si reguli coerente la termenele de predare.</p>
            </div>
        </div>
    </section>

    <section class="grid">
        <div class="card span-12">
            <h3>Ce gasesti in platforma</h3>
            <div class="friendly-grid">
                <div class="card">
                    <strong>Classroom-uri pe materii</strong>
                    <p class="muted">Fiecare proiect are context clar, membri definiti si reguli de lucru transparente.</p>
                </div>
                <div class="card">
                    <strong>Echipe si colaborare</strong>
                    <p class="muted">Invitatii, roluri si organizare eficienta pentru echipele studentesti.</p>
                </div>
                <div class="card">
                    <strong>Milestones si livrabile</strong>
                    <p class="muted">Termene vizibile, evaluare mai clara si ritm de lucru constant.</p>
                </div>
            </div>
        </div>

        <div class="card span-12">
            <h3>Cum incepi, pe scurt</h3>
            <div class="friendly-step">
                <div class="card">
                    <strong>1. Cadrul didactic configureaza proiectul</strong>
                    <p class="muted">Stabileste structura, etapele si termenele de lucru.</p>
                </div>
                <div class="card">
                    <strong>2. Studentii se organizeaza in echipe</strong>
                    <p class="muted">Distribuie responsabilitati si pornesc activitatea in proiect.</p>
                </div>
                <div class="card">
                    <strong>3. Progresul este vizibil pentru toti</strong>
                    <p class="muted">Comunicare mai clara si decizii luate la timp.</p>
                </div>
            </div>
        </div>

        <div class="card span-12">
            <div class="landing-cta">
                <div>
                    <h3 style="margin:0 0 6px;">Vrei sa folosesti platforma pentru grupa ta?</h3>
                    <p style="margin:0;color:#ffedd5;">Solicita acces administratorului si incepe organizarea proiectelor intr-un cadru unitar.</p>
                </div>
                <a class="btn btn-secondary" href="{{ route('login') }}">Autentificare</a>
            </div>
        </div>
    </section>
@endsection
