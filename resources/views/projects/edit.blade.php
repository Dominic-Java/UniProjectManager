@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Proiect</div>
        <h1>Editeaza proiect</h1>
        <p>Actualizeaza informatiile proiectului.</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if ($errors->any())
                <div class="notice error" style="margin-bottom:12px;">
                    Verifica datele introduse si incearca din nou.
                </div>
            @endif

            <form method="POST" action="{{ route('projects.update', $project) }}">
                @csrf
                @method('PUT')

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

                <div style="margin-bottom:12px;">
                    <label class="muted">Domeniu (optional)</label>
                    <input type="text" name="domain" value="{{ old('domain', $project->domain) }}" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">
                </div>

                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="muted">Data inceput</label>
                        <input type="date" name="start_date" value="{{ old('start_date', optional($project->start_date)->format('Y-m-d')) }}" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">
                    </div>
                    <div>
                        <label class="muted">Data finalizare</label>
                        <input type="date" name="end_date" value="{{ old('end_date', optional($project->end_date)->format('Y-m-d')) }}" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">
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
                    <button type="submit" class="btn btn-primary">Salveaza modificari</button>
                    <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">Inapoi</a>
                </div>
            </form>
        </div>

        <div class="card span-4">
            <h3>Info</h3>
            <p class="muted">Modificarile sunt vizibile imediat in proiect.</p>
        </div>
    </section>
@endsection
