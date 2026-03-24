@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Echipa</div>
        <h1>Actualizeaza datele echipei</h1>
        <p>Poti modifica numele echipei si statutul curent.</p>
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
                        <option value="active" @selected(old('status', $team->status) === 'active')>Activa</option>
                        <option value="locked" @selected(old('status', $team->status) === 'locked')>Blocata</option>
                        <option value="archived" @selected(old('status', $team->status) === 'archived')>Arhivata</option>
                    </select>
                </div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary">Salveaza modificarile</button>
                    <a href="{{ route('teams.show', $team) }}" class="btn btn-secondary">Inapoi la detalii</a>
                </div>
            </form>
        </div>
    </section>
@endsection
