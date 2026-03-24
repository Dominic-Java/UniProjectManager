@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Proiect</div>
        <h1>Actualizeaza proiectul</h1>
        <p>Revizuieste detaliile proiectului si salveaza modificarile necesare.</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if ($errors->any())
                <div class="notice error" style="margin-bottom:12px;">
                    Te rugam sa verifici datele introduse si sa incerci din nou.
                </div>
            @endif

            <form method="POST" action="{{ route('projects.update', $project) }}">
                @csrf
                @method('PUT')

                <div style="margin-bottom:12px;">
                    <label class="muted">Classroom</label>
                    <select name="classroom_id" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">
                        <option value="">-- fara classroom asociat (legacy) --</option>
                        @foreach($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" @selected((string) old('classroom_id', $project->classroom_id) === (string) $classroom->id)>
                                {{ $classroom->name }} - {{ $classroom->subject }} ({{ $classroom->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom:12px;">
                    <label class="muted">Titlu proiect</label>
                    <input type="text" name="title" value="{{ old('title', $project->title) }}" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);" required>
                </div>

                <div style="margin-bottom:12px;">
                    <label class="muted">Cod proiect (optional)</label>
                    <input type="text" name="code" value="{{ old('code', $project->code) }}" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">
                </div>

                <div style="margin-bottom:12px;">
                    <label class="muted">Descriere</label>
                    <textarea name="description" rows="4" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);" required>{{ old('description', $project->description) }}</textarea>
                </div>

                @if($project->classroom_id)
                    <div class="notice" style="margin-bottom:12px;">
                        Materia este preluata automat din classroom: <strong>{{ $project->domain ?: '-' }}</strong>
                    </div>
                @else
                    <div style="margin-bottom:12px;">
                        <label class="muted">Materie / disciplina</label>
                        <input type="text" name="domain" value="{{ old('domain', $project->domain) }}" placeholder="Ex: Ingineria Programarii" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);" required>
                    </div>
                @endif

                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="muted">Data inceput</label>
                        <input type="date" name="start_date" value="{{ old('start_date', optional($project->start_date)->format('Y-m-d')) }}" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">
                    </div>
                    <div>
                        <label class="muted">Data finalizare</label>
                        <input type="date" name="end_date" value="{{ old('end_date', optional($project->end_date)->format('Y-m-d')) }}" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">
                    </div>
                    <div>
                        <label class="muted">Data deadline inchidere</label>
                        <input type="date" name="deadline_date" value="{{ old('deadline_date', optional($project->deadline_at)->format('Y-m-d')) }}" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">
                    </div>
                    <div>
                        <label class="muted">Ora deadline</label>
                        @php($selectedDeadlineTime = old('deadline_time', optional($project->deadline_at)->format('H:i')))
                        <input
                            type="text"
                            name="deadline_time"
                            value="{{ $selectedDeadlineTime }}"
                            placeholder="Ex: 18:30"
                            list="deadline-time-options-edit"
                            inputmode="numeric"
                            pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$"
                            style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);"
                        >
                        <datalist id="deadline-time-options-edit">
                            @for($hour = 0; $hour < 24; $hour++)
                                @for($minute = 0; $minute < 60; $minute++)
                                    @php($timeValue = sprintf('%02d:%02d', $hour, $minute))
                                    <option value="{{ $timeValue }}"></option>
                                @endfor
                            @endfor
                        </datalist>
                        <p class="muted" style="margin-top:8px;">Poti selecta ora din lista sau o poti introduce manual.</p>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="muted">Min membri echipa</label>
                        <input type="number" min="1" max="20" name="min_team_size" value="{{ old('min_team_size', $project->min_team_size) }}" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">
                    </div>
                    <div>
                        <label class="muted">Max membri echipa</label>
                        <input type="number" min="1" max="20" name="max_team_size" value="{{ old('max_team_size', $project->max_team_size) }}" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                    <div>
                        <label class="muted">Vizibilitate</label>
                        <select name="visibility" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">
                            <option value="public" @selected(old('visibility', $project->visibility) === 'public')>Public</option>
                            <option value="private" @selected(old('visibility', $project->visibility) === 'private')>Privat</option>
                        </select>
                    </div>
                    <div>
                        <label class="muted">Status</label>
                        <select name="status" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">
                            <option value="draft" @selected(old('status', $project->status) === 'draft')>Draft</option>
                            <option value="open" @selected(old('status', $project->status) === 'open')>Open</option>
                            <option value="in_progress" @selected(old('status', $project->status) === 'in_progress')>In progress</option>
                            <option value="closed" @selected(old('status', $project->status) === 'closed')>Closed</option>
                            <option value="archived" @selected(old('status', $project->status) === 'archived')>Archived</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary">Salveaza modificarile</button>
                    <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">Revino la proiect</a>
                </div>
            </form>
        </div>

        <div class="card span-4">
            <h3>Informare</h3>
            <p class="muted">Modificarile salvate devin vizibile imediat pentru utilizatorii care au acces la proiect.</p>
        </div>
    </section>
@endsection
