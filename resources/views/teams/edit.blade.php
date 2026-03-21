@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Echipa</div>
        <h1>Editeaza echipa</h1>
        <p>Actualizeaza numele si statusul.</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if (session('error'))
                <div class="notice error" style="margin-bottom:12px;">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="notice error" style="margin-bottom:12px;">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('teams.update', $team) }}">
                @csrf
                @method('PUT')

                <div style="margin-bottom:12px;">
                    <label class="label" for="name">Nume echipa</label>
                    <input class="input" id="name" type="text" name="name" value="{{ old('name', $team->name) }}" required>
                </div>

                <div style="margin-bottom:12px;">
                    <label class="label" for="status">Status</label>
                    <select class="input" id="status" name="status">
                        <option value="active" @selected(old('status', $team->status) === 'active')>Active</option>
                        <option value="locked" @selected(old('status', $team->status) === 'locked')>Locked</option>
                        <option value="archived" @selected(old('status', $team->status) === 'archived')>Archived</option>
                    </select>
                </div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary">Salveaza</button>
                    <a href="{{ route('teams.show', $team) }}" class="btn btn-secondary">Inapoi</a>
                </div>
            </form>
        </div>
    </section>
@endsection
