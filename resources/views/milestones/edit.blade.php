@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Etapa</div>
        <h1>Actualizeaza etapa</h1>
        <p>Revizuieste detaliile pentru a pastra calendarul proiectului clar.</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if (session('error'))
                <div class="notice error" style="margin-bottom:12px;">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="notice error" style="margin-bottom:12px;">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('milestones.update', $milestone) }}">
                @csrf
                @method('PUT')

                <div style="margin-bottom:12px;">
                    <label class="label" for="project_id">Proiect</label>
                    <select class="input" id="project_id" name="project_id" required>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" @selected(old('project_id', $milestone->project_id) == $project->id)>{{ $project->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom:12px;">
                    <label class="label" for="title">Titlu</label>
                    <input class="input" id="title" type="text" name="title" value="{{ old('title', $milestone->title) }}" required>
                </div>

                <div style="margin-bottom:12px;">
                    <label class="label" for="description">Descriere</label>
                    <textarea class="input" id="description" name="description" rows="4">{{ old('description', $milestone->description) }}</textarea>
                </div>

                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="label" for="due_at">Termen limita</label>
                        <input class="input" id="due_at" type="datetime-local" name="due_at" value="{{ old('due_at', optional($milestone->due_at)->format('Y-m-d\\TH:i')) }}">
                    </div>
                    <div>
                        <label class="label" for="weight">Pondere</label>
                        <input class="input" id="weight" type="number" step="0.01" name="weight" value="{{ old('weight', $milestone->weight) }}" required>
                    </div>
                </div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary">Salveaza modificarile</button>
                    <a href="{{ route('milestones.show', $milestone) }}" class="btn btn-secondary">Inapoi la detalii</a>
                </div>
            </form>
        </div>
    </section>
@endsection
