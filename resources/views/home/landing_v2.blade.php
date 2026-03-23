@extends('layouts.app')

@push('head')
<style>
    .landing-wrap {
        display: grid;
        gap: 24px;
        padding: 36px 0 28px;
    }
    .landing-hero {
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
        .benefits-grid {
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
            <div class="pill">Bun venit in UniProjectManager</div>
            <h1>Proiectele tale, fără bătăi de cap.</h1>
            <p>UniProjectManager te ajută să ții totul organizat – task-uri, echipe și deadline-uri, într-un singur loc.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="{{ route('login') }}">Intră în cont</a>
            </div>
        </div>

        <div class="benefits-grid">
            <div class="benefit-card">
                <h3>Știi mereu ce ai de făcut</h3>
                <p>Task-urile sunt clare și ușor de urmărit.</p>
            </div>
            <div class="benefit-card">
                <h3>Lucrezi mai ușor în echipă</h3>
                <p>Toată lumea e pe aceeași pagină.</p>
            </div>
            <div class="benefit-card">
                <h3>Fără stres inutil</h3>
                <p>Gata cu mesajele pierdute și confuzia.</p>
            </div>
        </div>

        <div class="steps-card">
            <h2>Cum începi</h2>
            <ol class="steps-list">
                <li>Profesorul creează proiectul</li>
                <li>Intri în echipă</li>
                <li>Lucrezi și încarci livrabilele</li>
                <li>Vezi progresul în timp real</li>
            </ol>
        </div>

        <div class="final-cta">
            <p>Încearcă și vezi cât de simplu poate fi.</p>
            <a class="btn btn-secondary" href="{{ route('login') }}">Intră acum</a>
        </div>
    </section>
@endsection
