@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Livrabil nou</div>
        <h1>Creeaza livrabil</h1>
        <p>Completeaza informatiile livrabilului.</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if ($errors->any())
                <div class="notice error" style="margin-bottom:12px;">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('deliverables.store') }}">
                @csrf

                <div style="margin-bottom:12px;">
                    <label class="label" for="project_id">Proiect</label>
                    <select class="input" id="project_id" name="project_id" required>
                        <option value="">-- alege proiect --</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>{{ $project->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom:12px;">
                    <label class="label" for="milestone_id">Milestone (optional)</label>
                    <select class="input" id="milestone_id" name="milestone_id">
                        <option value="">-- fara milestone --</option>
                        @foreach($milestones as $milestone)
                            <option value="{{ $milestone->id }}" @selected(old('milestone_id') == $milestone->id)>{{ $milestone->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom:12px;">
                    <label class="label" for="title">Titlu</label>
                    <input class="input" id="title" type="text" name="title" value="{{ old('title') }}" required>
                </div>

                <div style="margin-bottom:12px;">
                    <label class="label" for="description">Descriere</label>
                    <textarea class="input" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                </div>

                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="label" for="due_at">Deadline</label>
                        <input class="input" id="due_at" type="datetime-local" name="due_at" value="{{ old('due_at') }}">
                    </div>
                    <div>
                        <label class="label" for="max_points">Punctaj maxim</label>
                        <input class="input" id="max_points" type="number" step="0.01" name="max_points" value="{{ old('max_points', 100) }}" required>
                    </div>
                </div>

                <div style="margin-bottom:12px;">
                    <label class="label" for="submission_type">Tip predare</label>
                    <select class="input" id="submission_type" name="submission_type">
                        <option value="file" @selected(old('submission_type') === 'file')>Fisier</option>
                        <option value="link" @selected(old('submission_type') === 'link')>Link</option>
                        <option value="both" @selected(old('submission_type') === 'both')>Ambele</option>
                    </select>
                </div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary">Salveaza livrabil</button>
                    <a href="{{ route('deliverables.index') }}" class="btn btn-secondary">Inapoi</a>
                </div>
            </form>
        </div>
    </section>
@endsection
