@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Echipa noua</div>
        <h1>Creeaza echipa</h1>
        <p>Alege proiectul si numele echipei.</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if (session('error'))
                <div class="notice error" style="margin-bottom:12px;">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="notice error" style="margin-bottom:12px;">{{ $errors->first() }}</div>
            @endif

            @if ($projects->count() === 0)
                <div class="notice">Nu exista proiecte deschise pentru care poti crea echipa.</div>
            @else
                <form method="POST" action="{{ route('teams.store') }}">
                    @csrf

                <div style="margin-bottom:12px;">
                    <label class="label" for="project_id">Proiect</label>
                    <select class="input" id="project_id" name="project_id" required>
                        <option value="">-- alege proiect --</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>
                                {{ $project->title }}
                                @if($project->domain)
                                    - {{ $project->domain }}
                                @endif
                                ({{ $project->min_team_size }}-{{ $project->max_team_size }} membri)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom:12px;">
                    <label class="label" for="name">Nume echipa</label>
                    <input class="input" id="name" type="text" name="name" value="{{ old('name') }}" required>
                </div>

                <div style="margin-bottom:12px;">
                    <label class="label" for="status">Status</label>
                    <select class="input" id="status" name="status">
                        <option value="active" @selected(old('status') === 'active')>Active</option>
                        <option value="locked" @selected(old('status') === 'locked')>Locked</option>
                        <option value="archived" @selected(old('status') === 'archived')>Archived</option>
                    </select>
                </div>

                @if(auth()->user()?->hasRole('profesor'))
                    <div style="margin-bottom:12px;">
                        <label class="label" for="captain_user_id">Capitan echipa (student)</label>
                        <select class="input" id="captain_user_id" name="captain_user_id" required>
                            <option value="">-- selecteaza student --</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" @selected(old('captain_user_id') == $student->id)>
                                    {{ $student->name }} ({{ $student->email }})
                                </option>
                            @endforeach
                        </select>
                        <p class="muted" style="margin-top:6px;">Profesorul poate crea echipa, dar membrii raman doar studentii.</p>
                    </div>
                @endif

                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <button type="submit" class="btn btn-primary">Salveaza echipa</button>
                        <a href="{{ route('teams.index') }}" class="btn btn-secondary">Inapoi</a>
                    </div>
                </form>
            @endif
        </div>

        <div class="card span-4">
            <h3>Info</h3>
            <p class="muted">Membrii echipei pot fi doar studenti.</p>
            <p class="muted">Capitanul (liderul) poate trimite invitatii si adauga membri.</p>
        </div>
    </section>
@endsection
