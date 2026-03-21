@extends('layouts.app')

@push('head')
<style>
    .student-actions {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }
    .student-action {
        border: 1px solid rgba(249, 115, 22, 0.18);
        border-radius: 14px;
        background: #fff7ed;
        padding: 14px;
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

    <section class="grid">
        <div class="card span-8" style="background:linear-gradient(120deg,#fff7ed 0%, #ffedd5 58%, #fed7aa 100%);">
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

        <div class="card span-12" style="background:#7c2d12;color:#ffedd5;">
            <strong>Flux student simplu:</strong>
            <div style="margin-top:8px;">
                Alatura-te unei echipe -> urmareste milestones -> trimite livrabile -> primeste feedback.
            </div>
        </div>
    </section>
@endsection
