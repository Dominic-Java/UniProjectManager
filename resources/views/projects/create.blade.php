@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Proiect nou</div>
        <h1>Creeaza proiect</h1>
        <p>Completeaza informatiile de baza pentru un proiect studentesc.</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if (session('error'))
                <div class="notice" style="margin-bottom:12px;">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="notice" style="margin-bottom:12px;">
                    Verifica campurile marcate.
                </div>
            @endif

            <form method="POST" action="{{ route('projects.store') }}">
                @csrf

                <div style="margin-bottom:12px;">
                    <label class="muted">Titlu proiect</label>
                    <input type="text" name="title" value="{{ old('title') }}" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);" required>
                </div>

                <div style="margin-bottom:12px;">
                    <label class="muted">Descriere</label>
                    <textarea name="description" rows="4" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">{{ old('description') }}</textarea>
                </div>

                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                    <div>
                        <label class="muted">Data inceput</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">
                    </div>
                    <div>
                        <label class="muted">Data finalizare</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" style="width:100%;padding:10px;border-radius:10px;border:1px solid var(--line);">
                    </div>
                </div>

                <div style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary">Salveaza proiect</button>
                    <a href="{{ route('projects.index') }}" class="btn btn-secondary">Inapoi la lista</a>
                </div>
            </form>
        </div>

        <div class="card span-4">
            <h3>Recomandari</h3>
            <ul>
                <li>Defineste tema proiectului.</li>
                <li>Adauga un interval realist.</li>
                <li>Completeaza descrierea pentru echipa.</li>
            </ul>
        </div>
    </section>
@endsection
