@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Proiect</div>
        <h1>{{ $project->title }}</h1>
        <p>{{ $project->description }}</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if (session('success'))
                <div class="notice success" style="margin-bottom:12px;">{{ session('success') }}</div>
            @endif

            <table class="table">
                <tbody>
                <tr>
                    <th>Cod</th>
                    <td>{{ $project->code ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Domeniu</th>
                    <td>{{ $project->domain ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>{{ $project->status }}</td>
                </tr>
                <tr>
                    <th>Vizibilitate</th>
                    <td>{{ $project->visibility }}</td>
                </tr>
                <tr>
                    <th>Dimensiune echipa</th>
                    <td>{{ $project->min_team_size }} - {{ $project->max_team_size }}</td>
                </tr>
                <tr>
                    <th>Interval</th>
                    <td>{{ $project->start_date ?? '-' }} - {{ $project->end_date ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Creat de</th>
                    <td>{{ $project->createdBy?->name ?? 'n/a' }}</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="card span-4">
            <h3>Actiuni</h3>
            <div style="display:flex;flex-direction:column;gap:8px;">
                @if(auth()->user()?->hasRole('profesor'))
                    <a class="btn btn-secondary" href="{{ route('projects.edit', $project) }}">Editeaza proiect</a>
                @endif
                <a class="btn btn-secondary" href="{{ route('projects.index') }}">Inapoi la lista</a>
            </div>
        </div>

        <div class="card span-4">
            <h3>Echipe</h3>
            <p class="muted">{{ $project->teams->count() }} echipe asociate</p>
            <ul>
                @foreach($project->teams->take(5) as $team)
                    <li>{{ $team->name }} ({{ $team->status }})</li>
                @endforeach
            </ul>
        </div>

        <div class="card span-4">
            <h3>Milestones</h3>
            <p class="muted">{{ $project->milestones->count() }} etape</p>
            <ul>
                @foreach($project->milestones->take(5) as $ms)
                    <li>{{ $ms->title }} ({{ $ms->due_at ?? '-' }})</li>
                @endforeach
            </ul>
        </div>

        <div class="card span-4">
            <h3>Livrabile</h3>
            <p class="muted">{{ $project->deliverables->count() }} livrabile</p>
            <ul>
                @foreach($project->deliverables->take(5) as $del)
                    <li>{{ $del->title }} ({{ $del->due_at ?? '-' }})</li>
                @endforeach
            </ul>
        </div>

        <div class="card span-6">
            <h3>Cerinte proiect</h3>
            @if($project->requirements->count() === 0)
                <div class="notice">Nu exista cerinte inregistrate.</div>
            @else
                <ul>
                    @foreach($project->requirements->take(5) as $req)
                        <li>{{ $req->title }} (v{{ $req->version }})</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="card span-6">
            <h3>Staff</h3>
            @if($project->staff->count() === 0)
                <div class="notice">Nu exista profesori asignati.</div>
            @else
                <ul>
                    @foreach($project->staff->take(5) as $staff)
                        <li>{{ $staff->name }} - {{ $staff->pivot->staff_role }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>
@endsection
