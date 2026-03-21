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
    .landing-side {
        display: grid;
        gap: 12px;
    }
    .landing-side .card {
        padding: 18px;
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
            <h1>Un loc simplu unde profesorii si studentii lucreaza impreuna, fara haos.</h1>
            <p>
                Gasesti proiectele, etapele si livrabilele intr-un singur flux clar.
                Fiecare echipa stie ce are de facut, iar profesorii vad progresul fara sa alerge dupa update-uri.
            </p>
            <div class="landing-buttons">
                <a class="btn btn-primary" href="{{ route('login') }}">Intra in platforma</a>
                <a class="btn btn-secondary" href="{{ route('landing', ['v' => 2]) }}">Vezi varianta 2</a>
                <span class="muted" style="align-self:center;">Conturile sunt create de administrator.</span>
            </div>
            <div class="landing-tags">
                <span class="landing-tag">Proiecte organizate</span>
                <span class="landing-tag">Echipe active</span>
                <span class="landing-tag">Deadline-uri clare</span>
            </div>
        </div>

        <div class="landing-side">
            <div class="card">
                <strong>Stil classroom, dar pe proiecte reale</strong>
                <p class="muted">Mai putin timp pierdut pe mesaje disparate, mai mult focus pe ce conteaza.</p>
            </div>
            <div class="card">
                <strong>Prietenos pentru studenti</strong>
                <p class="muted">Interfata curata, actiuni rapide si informatii la vedere.</p>
            </div>
            <div class="card">
                <strong>Control bun pentru profesori</strong>
                <p class="muted">Roluri, audit log si reguli clare cand proiectele ajung la termen.</p>
            </div>
        </div>
    </section>

    <section class="grid">
        <div class="card span-12">
            <h3>Ce gasesti in platforma</h3>
            <div class="friendly-grid">
                <div class="card">
                    <strong>Clase de proiect</strong>
                    <p class="muted">Fiecare proiect are context clar, membri si reguli de lucru.</p>
                </div>
                <div class="card">
                    <strong>Echipe si colaborare</strong>
                    <p class="muted">Invitatii, roluri si organizare usoara pentru echipele studentesti.</p>
                </div>
                <div class="card">
                    <strong>Milestones si livrabile</strong>
                    <p class="muted">Deadline-uri vizibile, evaluare mai simpla si ritm de lucru stabil.</p>
                </div>
            </div>
        </div>

        <div class="card span-12">
            <h3>Cum incepi, pe scurt</h3>
            <div class="friendly-step">
                <div class="card">
                    <strong>1. Profesorul pregateste proiectul</strong>
                    <p class="muted">Seteaza structura, etapele si termenele.</p>
                </div>
                <div class="card">
                    <strong>2. Studentii intra in echipe</strong>
                    <p class="muted">Se organizeaza rapid si pornesc pe taskuri.</p>
                </div>
                <div class="card">
                    <strong>3. Toata lumea vede progresul</strong>
                    <p class="muted">Mai putine neintelegeri, mai multa claritate.</p>
                </div>
            </div>
        </div>

        <div class="card span-12">
            <div class="landing-cta">
                <div>
                    <h3 style="margin:0 0 6px;">Vrei sa folosesti platforma pentru grupa ta?</h3>
                    <p style="margin:0;color:#ffedd5;">Cere acces de la administrator si intra direct in lucru.</p>
                </div>
                <a class="btn btn-secondary" href="{{ route('login') }}">Autentificare</a>
            </div>
        </div>
    </section>
@endsection
