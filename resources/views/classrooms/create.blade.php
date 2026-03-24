@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Classroom nou</div>
        <h1>Configureaza un classroom</h1>
        <p>Dupa salvare, codul clasei se genereaza automat si poate fi trimis studentilor.</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if ($errors->any())
                <div class="notice error" style="margin-bottom:12px;">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('classrooms.store') }}">
                @csrf

                <div style="margin-bottom:12px;">
                    <label class="label" for="name">Nume clasa</label>
                    <input class="input" id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Ex: Seria A - Semestrul 2" required>
                </div>

                <div style="margin-bottom:12px;">
                    <label class="label" for="subject">Materie</label>
                    <input class="input" id="subject" name="subject" type="text" value="{{ old('subject') }}" placeholder="Ex: Ingineria Programarii" required>
                </div>

                <div style="margin-bottom:12px;">
                    <label class="label" for="description">Descriere (optional)</label>
                    <textarea class="input" id="description" name="description" rows="4" placeholder="Detalii pentru studenti">{{ old('description') }}</textarea>
                </div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button class="btn btn-primary" type="submit">Creeaza classroom</button>
                    <a class="btn btn-secondary" href="{{ route('classrooms.index') }}">Inapoi</a>
                </div>
            </form>
        </div>

        <div class="card span-4">
            <h3>Recomandare de lucru</h3>
            <ul>
                <li>Creeaza classroom-ul pentru disciplina predata.</li>
                <li>Invita studentii prin email sau prin codul clasei.</li>
                <li>Adauga proiectele direct in classroom-ul creat.</li>
            </ul>
        </div>
    </section>
@endsection
