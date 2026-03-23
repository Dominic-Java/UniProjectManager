@extends('layouts.app')

@push('head')
<style>
    .home-grid .card {
        background: linear-gradient(180deg, #fff8ee 0%, #fff2e3 100%);
        border-color: rgba(234, 88, 12, 0.2);
    }
    body[data-theme="dark"] .home-grid .card {
        background: linear-gradient(180deg, #1f2a3d 0%, #182234 100%);
        border-color: #334155;
    }
    .student-highlight {
        background: linear-gradient(125deg, #ffe9cf 0%, #ffd8b5 55%, #fdba74 100%) !important;
        border-color: rgba(234, 88, 12, 0.34) !important;
    }
    body[data-theme="dark"] .student-highlight {
        background: linear-gradient(125deg, #0f172a 0%, #1e293b 55%, #334155 100%) !important;
        border-color: #475569 !important;
    }
    .student-flow {
        background: linear-gradient(145deg, #7c2d12 0%, #9a3412 100%) !important;
        color: #ffedd5;
        border-color: rgba(255, 237, 213, 0.25) !important;
    }
    body[data-theme="dark"] .student-flow {
        background: linear-gradient(145deg, #0b1328 0%, #1e293b 100%) !important;
        color: #dbeafe;
        border-color: #475569 !important;
    }
    .student-actions {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }
    .student-action {
        border: 1px solid rgba(249, 115, 22, 0.26);
        border-radius: 14px;
        background: rgba(255, 250, 242, 0.55);
        padding: 14px;
    }
    body[data-theme="dark"] .student-action {
        border-color: #475569;
        background: rgba(15, 23, 42, 0.55);
    }
    @media (max-width: 900px) {
        .student-actions { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
    <section class="hero">
        <div class="pill">Student</div>
        <h1>{{ $subtitle }}</h1>
        <p>Bine ai revenit! Aici vezi clar ce ai de facut si ce urmeaza.</p>
    </section>

    <section class="grid home-grid">
        <div class="card span-8 student-highlight">
            <h3>Planul tau de lucru</h3>
            <ul>
                @foreach($highlights as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
            <p class="muted">Ramai aproape de echipa si urmareste termenele din proiect.</p>
        </div>

        <div class="card span-4">
            <h3>Contul tau</h3>
            <p class="muted">Rolul tau este <strong>Student</strong>.</p>
            <div class="notice" style="margin-top:10px;">
                Conturile sunt create de administrator, nu prin inregistrare publica.
            </div>
            <p class="muted" style="margin-top:8px;">
                Pentru acces nou sau schimbari de rol, contacteaza profesorul/adminul responsabil.
            </p>
        </div>

        <div class="card span-12">
            <h3>Proiectele tale</h3>
            @if(empty($recent_projects))
                <div class="notice">Nu esti inca intr-un proiect activ. Verifica invitatiile de echipa.</div>
            @else
                <table class="table">
                    <thead>
                    <tr>
                        <th>Proiect</th>
                        <th>Materie</th>
                        <th>Status</th>
                        <th>Deadline</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($recent_projects as $project)
                        <tr>
                            <td>{{ $project['title'] }}</td>
                            <td>{{ $project['subject'] ?: '-' }}</td>
                            <td>{{ $project['status'] }}</td>
                            <td>{{ $project['deadline_at'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card span-12">
            <h3>Intra rapid unde ai nevoie</h3>
            <div class="student-actions">
                @foreach($actions as $a)
                    <div class="student-action">
                        <strong>{{ $a['label'] }}</strong>
                        <div style="margin-top:10px;">
                            <a class="btn btn-primary" href="{{ $a['href'] }}">Deschide</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card span-12 student-flow">
            <strong>Flux student simplu:</strong>
            <div style="margin-top:8px;">
                Alatura-te unei echipe -> urmareste milestones -> trimite livrabile -> primeste feedback.
            </div>
        </div>
    </section>
@endsection
