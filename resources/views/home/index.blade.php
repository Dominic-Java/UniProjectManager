@extends('layouts.app')

@push('head')
<style>
    .dashboard-cards {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-top: 14px;
    }
    .dashboard-card {
        border: 1px solid rgba(249, 115, 22, 0.18);
        border-radius: 16px;
        padding: 14px 16px;
        background: #fffdfa;
    }
    .classroom-actions {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-top: 12px;
    }
    .classroom-action {
        border-radius: 14px;
        border: 1px solid rgba(249, 115, 22, 0.22);
        background: #fff7ed;
        padding: 14px;
    }
    @media (max-width: 900px) {
        .dashboard-cards,
        .classroom-actions {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
    <section class="hero">
        <div class="pill">Profesor</div>
        <h1>{{ $subtitle }}</h1>
        <p>Ai intr-un singur loc classroom-urile pe materii, echipele si livrabilele.</p>
    </section>

    <section class="grid">
        <div class="card span-12" style="background:linear-gradient(120deg,#fff7ed 0%, #ffedd5 58%, #fed7aa 100%);">
            <h3>Pornire rapida</h3>
            <p class="muted">Deschide direct ce ai nevoie, fara pasi inutili.</p>
            <div class="classroom-actions">
                @foreach($quick_actions as $a)
                    <div class="classroom-action">
                        <strong>{{ $a['label'] }}</strong>
                        <div style="margin-top:10px;">
                            <a class="btn btn-primary" href="{{ $a['href'] }}">Deschide</a>
                        </div>
                    </div>
                @endforeach
                @if(auth()->user()?->isAdmin())
                    <div class="classroom-action">
                        <strong>Setari utilizatori</strong>
                        <div style="margin-top:10px;">
                            <a class="btn btn-secondary" href="{{ route('settings.index') }}">Administreaza conturi</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card span-8">
            <h3>Snapshot pe semestru</h3>
            <p class="muted">O privire scurta ca sa stii imediat unde esti.</p>
            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <div class="muted">Proiecte</div>
                    <div style="font-size:28px;font-weight:800;margin-top:4px;">{{ $stats['projects'] }}</div>
                    <div class="muted">in lucru</div>
                </div>
                <div class="dashboard-card">
                    <div class="muted">Echipe</div>
                    <div style="font-size:28px;font-weight:800;margin-top:4px;">{{ $stats['teams'] }}</div>
                    <div class="muted">active</div>
                </div>
                <div class="dashboard-card">
                    <div class="muted">Livrabile</div>
                    <div style="font-size:28px;font-weight:800;margin-top:4px;">{{ $stats['deliverables'] }}</div>
                    <div class="muted">configurate</div>
                </div>
            </div>
        </div>

        <div class="card span-4">
            <h3>Contul tau</h3>
            <p class="muted">Rolul tau este <strong>Profesor</strong>.</p>
            <div class="notice" style="margin-top:10px;">
                Crearea conturilor este controlata de administrator.
            </div>
            <p class="muted" style="margin-top:8px;">
                Accesul nou se acorda doar prin fluxul din Setari, pe baza conturilor administrate.
            </p>
            <p class="muted">Daca ai nevoie de conturi noi, contacteaza administratorul platformei.</p>
        </div>

        <div class="card span-7">
            <h3>Classroom-uri pe materii</h3>
            @if(empty($recent_projects))
                <div class="notice">Nu ai inca proiecte create. Incepe cu primul classroom.</div>
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

        <div class="card span-5" style="background:#7c2d12;color:#ffedd5;">
            <h3 style="margin-top:0;">Ce merita facut azi</h3>
            <ul style="margin:8px 0 0;padding-left:18px;">
                @foreach($announcements as $msg)
                    <li>{{ $msg }}</li>
                @endforeach
            </ul>
        </div>
    </section>
@endsection
