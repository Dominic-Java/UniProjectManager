@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Proiect nou</div>
        <h1>Configureaza un proiect nou</h1>
        <p>Alege classroom-ul, completeaza detaliile si stabileste termenele de lucru.</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if (session('error'))
                <div class="notice" style="margin-bottom:12px;">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="notice" style="margin-bottom:12px;">
                    Te rugam sa verifici campurile marcate si sa incerci din nou.
                </div>
            @endif

            @if($classrooms->count() === 0)
                <div class="notice error">
                    Nu exista classroom-uri disponibile pentru creare proiect. Creeaza mai intai un classroom.
                    <div style="margin-top:10px;">
                        <a class="btn btn-primary" href="{{ route('classrooms.create') }}">Creeaza classroom</a>
                    </div>
                </div>
            @else

            <form method="POST" action="{{ route('projects.store') }}">
                @csrf
                @php($today = now()->toDateString())

                <div style="margin-bottom:12px;">
                    <label class="muted">Classroom</label>
                    <select name="classroom_id" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);" required>
                        <option value="">-- selecteaza classroom-ul --</option>
                        @foreach($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" @selected(old('classroom_id', $selected_classroom_id) == $classroom->id)>
                                {{ $classroom->name }} - {{ $classroom->subject }} ({{ $classroom->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom:12px;">
                    <label class="muted">Titlu proiect</label>
                    <input type="text" name="title" value="{{ old('title') }}" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);" required>
                </div>

                <div style="margin-bottom:12px;">
                    <label class="muted">Cod proiect (optional)</label>
                    <input type="text" name="code" value="{{ old('code') }}" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">
                </div>

                <div style="margin-bottom:12px;">
                    <label class="muted">Descriere</label>
                    <textarea name="description" rows="4" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);" required>{{ old('description') }}</textarea>
                </div>

                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="muted">Data inceput</label>
                        <input type="date" name="start_date" min="{{ $today }}" value="{{ old('start_date') }}" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">
                    </div>
                    <div>
                        <label class="muted">Data finalizare</label>
                        <input type="date" name="end_date" min="{{ $today }}" value="{{ old('end_date') }}" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">
                    </div>
                    <div>
                        <label class="muted">Data deadline inchidere</label>
                        <input type="date" name="deadline_date" min="{{ $today }}" value="{{ old('deadline_date') }}" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">
                    </div>
                    <div>
                        <label class="muted">Ora deadline</label>
                        <input
                            type="text"
                            name="deadline_time"
                            value="{{ old('deadline_time') }}"
                            placeholder="Ex: 18:30"
                            list="deadline-time-options-create"
                            inputmode="numeric"
                            pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$"
                            style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);"
                        >
                        <datalist id="deadline-time-options-create">
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
                        <input type="number" min="1" max="20" name="min_team_size" value="{{ old('min_team_size', 1) }}" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">
                    </div>
                    <div>
                        <label class="muted">Max membri echipa</label>
                        <input type="number" min="1" max="20" name="max_team_size" value="{{ old('max_team_size', 4) }}" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                    <div>
                        <label class="muted">Vizibilitate</label>
                        <select name="visibility" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">
                            <option value="public" @selected(old('visibility') === 'public')>Public</option>
                            <option value="private" @selected(old('visibility') === 'private')>Privat</option>
                        </select>
                    </div>
                    <div>
                        <label class="muted">Status</label>
                        <select name="status" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">
                            <option value="draft" @selected(old('status') === 'draft')>Draft</option>
                            <option value="open" @selected(old('status') === 'open')>Open</option>
                            <option value="in_progress" @selected(old('status') === 'in_progress')>In progress</option>
                            <option value="closed" @selected(old('status') === 'closed')>Closed</option>
                            <option value="archived" @selected(old('status') === 'archived')>Archived</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top:12px;padding:12px;border:1px dashed var(--line);border-radius:12px;">
                    <label style="display:flex;gap:8px;align-items:flex-start;font-weight:600;">
                        <input type="checkbox" name="is_retake_project" value="1" @checked(old('is_retake_project'))>
                        <span>Proiect dedicat pentru restante (vizibil doar studentilor cu nota sub 5 in aceasta materie)</span>
                    </label>
                    <p class="muted" style="margin-top:8px;">Cand este bifat, notificarile si accesul studentilor se limiteaza la cei restanti.</p>
                </div>

                <div style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary">Salveaza proiect</button>
                    <a href="{{ route('projects.index') }}" class="btn btn-secondary">Revino la lista proiectelor</a>
                </div>
            </form>
            @endif
        </div>

        <div class="card span-4">
            <h3>Recomandari utile</h3>
            <ul>
                <li>Selecteaza classroom-ul corect; materia se preia automat.</li>
                <li>Stabileste dimensiunea minima si maxima a echipei.</li>
                <li>Configureaza deadline-ul pentru inchiderea automata a proiectului.</li>
            </ul>
        </div>
    </section>
@endsection
