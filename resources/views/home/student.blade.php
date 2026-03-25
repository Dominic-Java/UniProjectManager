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
    .student-kpis {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }
    .student-kpi {
        border-radius: 14px;
        border: 1px solid rgba(249, 115, 22, 0.26);
        background: rgba(255, 250, 242, 0.55);
        padding: 14px;
    }
    body[data-theme="dark"] .student-kpi {
        border-color: #475569;
        background: rgba(15, 23, 42, 0.55);
    }
    .calendar-list {
        margin: 0;
        padding: 0;
        list-style: none;
        display: grid;
        gap: 10px;
    }
    .calendar-item {
        border: 1px solid rgba(249, 115, 22, 0.2);
        border-radius: 12px;
        padding: 10px 12px;
        background: rgba(255, 255, 255, 0.5);
    }
    body[data-theme="dark"] .calendar-item {
        border-color: #475569;
        background: rgba(15, 23, 42, 0.6);
    }
    .calendar-tag {
        display: inline-flex;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        background: #ffedd5;
        color: #9a3412;
    }
    body[data-theme="dark"] .calendar-tag {
        background: #1f2937;
        color: #f8fafc;
    }
    @media (max-width: 900px) {
        .student-actions,
        .student-kpis { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
    <section class="hero">
        <div class="pill">Student</div>
        <h1>{{ $subtitle }}</h1>
        <p>Aici gasesti activitatea ta curenta: proiecte, echipa si livrabilele care urmeaza.</p>
    </section>

    <section class="grid home-grid">
        <div class="card span-12 student-highlight">
            <h3>Actiuni rapide</h3>
            <p class="muted">Acceseaza direct sectiunile folosite cel mai des.</p>
            <div class="student-actions">
                @foreach($actions as $a)
                    <div class="student-action">
                        <strong>{{ $a['label'] }}</strong>
                        <div style="margin-top:10px;">
                            <a class="btn btn-primary" href="{{ $a['href'] }}">Acceseaza</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card span-12">
            <h3>Planul tau de lucru</h3>
            <ul>
                @foreach($highlights as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
            <p class="muted">Pastreaza ritmul echipei si verifica termenele importante in fiecare sesiune.</p>
        </div>

        <div class="card span-12">
            <h3>Rezumat rapid</h3>
            <div class="student-kpis">
                <div class="student-kpi">
                    <div class="muted">Proiecte vizibile</div>
                    <div style="font-size:28px;font-weight:800;margin-top:4px;">{{ count($recent_projects) }}</div>
                </div>
                <div class="student-kpi">
                    <div class="muted">Urmatorul pas</div>
                    <div style="margin-top:4px;font-weight:700;">Verifica deadline-urile apropiate</div>
                </div>
                <div class="student-kpi">
                    <div class="muted">Cursuri inscrise</div>
                    <div style="margin-top:4px;font-weight:700;">{{ count($courses) }}</div>
                </div>
            </div>
        </div>

        <div class="card span-6">
            <h3>Cursurile tale</h3>
            @if(empty($courses))
                <div class="notice">Nu esti inscris momentan in classroom-uri active.</div>
            @else
                <table class="table">
                    <thead>
                    <tr>
                        <th>Materie</th>
                        <th>Profesor</th>
                        <th>Cod</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($courses as $course)
                        <tr>
                            <td>{{ $course['subject'] }}<div class="muted">{{ $course['name'] }}</div></td>
                            <td>{{ $course['professor_name'] }}</td>
                            <td>{{ $course['code'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card span-6">
            <h3>Calendar academic</h3>
            @if(empty($calendar_events))
                <div class="notice">Nu exista evenimente viitoare in calendar.</div>
            @else
                <ul class="calendar-list">
                    @foreach($calendar_events as $event)
                        <li class="calendar-item">
                            <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                                <div>
                                    <span class="calendar-tag">{{ $event['tag'] }}</span>
                                    <div style="font-weight:700;margin-top:6px;">{{ $event['event_title'] }}</div>
                                    <div class="muted">{{ $event['subject'] }} - {{ $event['classroom_name'] }}</div>
                                </div>
                                <div style="font-weight:700;white-space:nowrap;">{{ $event['event_at'] ?? '-' }}</div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="card span-12">
            <h3>Proiectele tale</h3>
            @if(empty($recent_projects))
                <div class="notice">Nu ai proiecte active momentan. Verifica invitatiile primite in Classroom si Echipe.</div>
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

        <div class="card span-12 student-flow">
            <strong>Flux recomandat pentru studenti:</strong>
            <div style="margin-top:8px;">
                Intra in classroom -> colaboreaza cu echipa -> preda la timp -> urmareste feedback-ul.
            </div>
        </div>
    </section>
@endsection
