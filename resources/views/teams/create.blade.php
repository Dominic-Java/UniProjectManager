@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Echipa noua</div>
        <h1>Creeaza echipa</h1>
        <p>Alege proiectul si numele echipei.</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if ($errors->any())
                <div class="notice error" style="margin-bottom:12px;">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('teams.store') }}">
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

                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary">Salveaza echipa</button>
                    <a href="{{ route('teams.index') }}" class="btn btn-secondary">Inapoi</a>
                </div>
            </form>
        </div>

        <div class="card span-4">
            <h3>Info</h3>
            <p class="muted">Creatorul devine liderul echipei.</p>
        </div>
    </section>
@endsection
