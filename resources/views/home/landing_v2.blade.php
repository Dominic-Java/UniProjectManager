@extends('layouts.app')

@push('head')
<style>
    .landing-wrap {
        display: grid;
        gap: 20px;
        padding: 36px 0 28px;
    }
    .landing-hero {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 18px;
        border-radius: 24px;
        border: 1px solid rgba(251, 146, 60, 0.2);
        background: linear-gradient(145deg, #fffefb 0%, #fff7ed 60%, #ffeedc 100%);
        box-shadow: var(--shadow);
        padding: clamp(22px, 4vw, 40px);
    }
    body[data-theme="dark"] .landing-hero {
        border-color: #475569;
        background: linear-gradient(145deg, #0f172a 0%, #1e293b 60%, #334155 100%);
    }
    .landing-hero h1 {
        margin: 8px 0 10px;
        font-size: clamp(28px, 4.2vw, 52px);
        line-height: 1.1;
    }
    .landing-hero p {
        margin: 0;
        max-width: 70ch;
        color: var(--muted);
        font-size: clamp(15px, 2vw, 18px);
    }
    .hero-actions {
        margin-top: 22px;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    .hero-panel {
        border-radius: 16px;
        border: 1px solid rgba(251, 146, 60, 0.2);
        background: rgba(255, 255, 255, 0.65);
        padding: 16px;
        align-self: stretch;
    }
    body[data-theme="dark"] .hero-panel {
        border-color: #475569;
        background: rgba(15, 23, 42, 0.55);
    }
    .hero-panel-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 10px;
    }
    .hero-panel-item {
        border-radius: 12px;
        border: 1px solid rgba(249, 115, 22, 0.22);
        background: rgba(255, 250, 242, 0.55);
        padding: 10px 12px;
    }
    body[data-theme="dark"] .hero-panel-item {
        border-color: #475569;
        background: rgba(15, 23, 42, 0.55);
    }
    .benefits-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }
    .benefit-card {
        border-radius: 18px;
        border: 1px solid rgba(251, 146, 60, 0.18);
        background: #fffdfa;
        padding: 20px;
    }
    body[data-theme="dark"] .benefit-card {
        border-color: #475569;
        background: rgba(15, 23, 42, 0.55);
    }
    .benefit-card h3 {
        margin: 0 0 8px;
        font-size: 20px;
    }
    .benefit-card p {
        margin: 0;
        color: var(--muted);
    }
    .steps-card {
        border-radius: 20px;
        border: 1px solid rgba(251, 146, 60, 0.18);
        background: #fffaf4;
        padding: 22px;
    }
    body[data-theme="dark"] .steps-card {
        border-color: #475569;
        background: rgba(15, 23, 42, 0.55);
    }
    .steps-card h2 {
        margin: 0 0 14px;
    }
    .steps-list {
        margin: 0;
        padding-left: 20px;
        display: grid;
        gap: 10px;
    }
    .final-cta {
        border-radius: 20px;
        border: 1px solid rgba(251, 146, 60, 0.22);
        background: #7c2d12;
        color: #ffedd5;
        padding: 22px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
    }
    .final-cta p {
        margin: 0;
        font-size: clamp(18px, 2.5vw, 24px);
        font-weight: 700;
    }
    .final-cta .btn-secondary {
        background: #fff7ed;
        color: #9a3412;
    }
    body[data-theme="dark"] .final-cta {
        border-color: #475569;
        background: linear-gradient(140deg, #0b1328 0%, #1e293b 100%);
        color: #dbeafe;
    }
    body[data-theme="dark"] .final-cta .btn-secondary {
        background: #1e293b;
        color: #e2e8f0;
        border: 1px solid #475569;
    }
    @media (max-width: 960px) {
        .landing-hero {
            grid-template-columns: 1fr;
        }
        .benefits-grid {
            grid-template-columns: 1fr;
        }
        .hero-panel-grid {
            grid-template-columns: 1fr;
        }
        .final-cta {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
@endpush

@section('content')
    <section class="landing-wrap">
        <div class="landing-hero">
            <div>
                <div class="pill">Bine ai venit in UniProjectManager</div>
                <h1>Coordoneaza proiectele universitare intr-un cadru organizat!</h1>
                <p>
                    Cu UniProjectManager gestionezi intr-un singur loc clasele, proiectele, echipele si predarile.
                    Platforma te ajuta sa lucrezi organizat, cu termene vizibile si colaborare eficienta.
                </p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="{{ route('login') }}">Autentificare</a>
                </div>
            </div>
            <div class="hero-panel">
                <strong>Ce obtii din prima zi</strong>
                <div class="hero-panel-grid">
                    <div class="hero-panel-item">
                        <strong>Classroom-uri clare</strong>
                        <p class="muted" style="margin:6px 0 0;">Fiecare grupa are spatiul ei de lucru.</p>
                    </div>
                    <div class="hero-panel-item">
                        <strong>Termene vizibile</strong>
                        <p class="muted" style="margin:6px 0 0;">Deadline-urile raman centralizate.</p>
                    </div>
                    <div class="hero-panel-item">
                        <strong>Echipe organizate</strong>
                        <p class="muted" style="margin:6px 0 0;">Invitatii si roluri intr-un flux simplu.</p>
                    </div>
                    <div class="hero-panel-item">
                        <strong>Predari urmaribile</strong>
                        <p class="muted" style="margin:6px 0 0;">Livrabilele sunt usor de monitorizat.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="benefits-grid">
            <div class="benefit-card">
                <h3>Structura clara</h3>
                <p>Fiecare proiect este organizat pe etape, cu responsabilitati usor de urmarit.</p>
            </div>
            <div class="benefit-card">
                <h3>Colaborare eficienta</h3>
                <p>Studentii si profesorii lucreaza in acelasi flux, fara informatie dispersata.</p>
            </div>
            <div class="benefit-card">
                <h3>Termene controlate</h3>
                <p>Deadline-urile sunt vizibile si usor de gestionat pentru intreaga echipa.</p>
            </div>
        </div>

        <div class="steps-card">
            <h2>Cum incepi</h2>
            <ol class="steps-list">
                <li>Profesorul configureaza classroom-ul si proiectul.</li>
                <li>Studentii se alatura clasei si echipelor.</li>
                <li>Etapele si livrabilele sunt urmarite pana la final.</li>
                <li>Feedback-ul ramane centralizat pentru fiecare predare.</li>
            </ol>
        </div>

        <div class="final-cta">
            <p>Solicita acces si incepe activitatea in UniProjectManager.</p>
            <a class="btn btn-secondary" href="{{ route('login') }}">Intra in platforma</a>
        </div>
    </section>
@endsection
