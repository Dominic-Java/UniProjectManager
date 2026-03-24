@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Echipa noua</div>
        <h1>Creeaza o echipa</h1>
        <p>Alege proiectul si completeaza datele de baza pentru noua echipa.</p>
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
                <div class="notice">Momentan nu exista proiecte deschise pentru care poti crea o echipa.</div>
            @else
                <form method="POST" action="{{ route('teams.store') }}">
                    @csrf

                <div style="margin-bottom:12px;">
                    <label class="label" for="project_id">Proiect</label>
                    <select class="input" id="project_id" name="project_id" required>
                        <option value="">-- alege proiectul --</option>
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
                        <option value="active" @selected(old('status') === 'active')>Activa</option>
                        <option value="locked" @selected(old('status') === 'locked')>Blocata</option>
                        <option value="archived" @selected(old('status') === 'archived')>Arhivata</option>
                    </select>
                </div>

                @if(auth()->user()?->hasRole('profesor'))
                    <div style="margin-bottom:12px;">
                        <label class="label" for="captain_user_id">Lider echipa (student)</label>
                        <select class="input" id="captain_user_id" name="captain_user_id" required>
                            <option value="">-- alege studentul --</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" @selected(old('captain_user_id') == $student->id)>
                                    {{ $student->name }} ({{ $student->email }})
                                </option>
                            @endforeach
                        </select>
                        <p class="muted" style="margin-top:6px;">Pot fi alesi doar studenti inscrisi in classroom-ul proiectului.</p>
                    </div>
                @endif

                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <button type="submit" class="btn btn-primary">Creeaza echipa</button>
                        <a href="{{ route('teams.index') }}" class="btn btn-secondary">Inapoi la echipe</a>
                    </div>
                </form>
            @endif
        </div>

        <div class="card span-4">
            <h3>Informatii utile</h3>
            <p class="muted">Coordonarea echipei este facuta de liderul desemnat.</p>
            <p class="muted">Liderul poate trimite invitatii si poate actualiza componenta echipei.</p>
        </div>
    </section>
@endsection
