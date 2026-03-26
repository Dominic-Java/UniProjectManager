@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Proiecte</div>
        <h1>Proiectele platformei</h1>
        <p>Urmareste proiectele active si arhivate, cu acces rapid la detalii si editare.</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if (session('success'))
                <div class="notice success" style="margin-bottom:12px;">{{ session('success') }}</div>
            @endif

            @if (!$table_exists)
                <div class="notice">
                    Datele proiectelor nu sunt disponibile momentan. Verifica migrarile bazei de date.
                </div>
            @elseif (count($active_projects) === 0 && count($archived_projects) === 0)
                <div class="notice">
                    Nu exista proiecte inregistrate in acest moment.
                </div>
            @else
                <h3>Proiecte active</h3>
                @if(count($active_projects) === 0)
                    <div class="notice" style="margin-bottom:16px;">Nu exista proiecte active momentan.</div>
                @else
                    <table class="table" style="margin-bottom:16px;">
                        <thead>
                        <tr>
                            <th>Titlu</th>
                            <th>Classroom</th>
                            <th>Materie</th>
                            <th>Status</th>
                            <th>Deadline</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Creat</th>
                            <th>Actiuni</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($active_projects as $project)
                            <tr>
                                <td>
                                    {{ $project['title'] }}
                                    @if(!empty($project['is_retake_project']))
                                        <div class="muted" style="margin-top:4px;color:#b45309;font-weight:700;">Proiect restanta</div>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($project['classroom']))
                                        {{ $project['classroom'] }}
                                        @if(!empty($project['classroom_code']))
                                            ({{ $project['classroom_code'] }})
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $project['domain'] ?? '-' }}</td>
                                <td>{{ $project['status'] }}</td>
                                <td>{{ $project['deadline_at'] ?? '-' }}</td>
                                <td>{{ $project['start_date'] ?? '-' }}</td>
                                <td>{{ $project['end_date'] ?? '-' }}</td>
                                <td>{{ $project['created_at'] ?? '-' }}</td>
                                <td>
                                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                        <a class="btn btn-secondary" href="{{ route('projects.show', $project['id']) }}">Detalii</a>
                                        @if(auth()->user()?->hasRole('profesor') || auth()->user()?->isAdmin())
                                            <a class="btn btn-secondary" href="{{ route('projects.edit', $project['id']) }}">Editeaza</a>
                                            <form method="POST" action="{{ route('projects.destroy', $project['id']) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" onclick="return confirm('Vrei sa elimini acest proiect?')">Sterge</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif

                <h3>Arhivate</h3>
                @if(count($archived_projects) === 0)
                    <div class="notice">Nu exista proiecte arhivate.</div>
                @else
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Titlu</th>
                            <th>Classroom</th>
                            <th>Materie</th>
                            <th>Status</th>
                            <th>Deadline</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Creat</th>
                            <th>Actiuni</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($archived_projects as $project)
                            <tr>
                                <td>
                                    {{ $project['title'] }}
                                    @if(!empty($project['is_retake_project']))
                                        <div class="muted" style="margin-top:4px;color:#b45309;font-weight:700;">Proiect restanta</div>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($project['classroom']))
                                        {{ $project['classroom'] }}
                                        @if(!empty($project['classroom_code']))
                                            ({{ $project['classroom_code'] }})
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $project['domain'] ?? '-' }}</td>
                                <td>{{ $project['status'] }}</td>
                                <td>{{ $project['deadline_at'] ?? '-' }}</td>
                                <td>{{ $project['start_date'] ?? '-' }}</td>
                                <td>{{ $project['end_date'] ?? '-' }}</td>
                                <td>{{ $project['created_at'] ?? '-' }}</td>
                                <td>
                                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                        <a class="btn btn-secondary" href="{{ route('projects.show', $project['id']) }}">Detalii</a>
                                        @if(auth()->user()?->hasRole('profesor') || auth()->user()?->isAdmin())
                                            <a class="btn btn-secondary" href="{{ route('projects.edit', $project['id']) }}">Editeaza</a>
                                            <form method="POST" action="{{ route('projects.destroy', $project['id']) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" onclick="return confirm('Vrei sa elimini acest proiect?')">Sterge</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            @endif
        </div>

        <div class="card span-4">
            <h3>Actiuni</h3>
            <p class="muted">Configureaza mai intai classroom-urile, apoi proiectele asociate fiecarei clase.</p>
            @if(auth()->user()?->hasRole('profesor') || auth()->user()?->isAdmin())
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <a class="btn btn-primary" href="{{ route('classrooms.index') }}">Acceseaza classroom-urile</a>
                    <a class="btn btn-secondary" href="{{ route('projects.create') }}">Creeaza un proiect</a>
                </div>
            @else
                <div class="notice">
                    Crearea proiectelor este disponibila doar pentru cadrele didactice.
                </div>
            @endif
        </div>

        <div class="card span-12">
            <h3>Flux recomandat</h3>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                <div class="card" style="padding:14px;">
                    <strong>1. Defineste proiectul</strong>
                    <p class="muted" style="margin:8px 0 0;">Seteaza obiectivul, perioada si dimensiunea echipei.</p>
                </div>
                <div class="card" style="padding:14px;">
                    <strong>2. Stabileste etapele</strong>
                    <p class="muted" style="margin:8px 0 0;">Adauga milestones si livrabile pentru ritm constant.</p>
                </div>
                <div class="card" style="padding:14px;">
                    <strong>3. Monitorizeaza progresul</strong>
                    <p class="muted" style="margin:8px 0 0;">Urmareste echipele, predarile si ajustarile necesare.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
