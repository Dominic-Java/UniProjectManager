@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Cont nou</div>
        <h1>Inregistrare utilizator</h1>
        <p>Creeaza un cont pentru a accesa proiectele universitare.</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if ($errors->any())
                <div class="notice error" style="margin-bottom:12px;">
                    @if ($errors->first() === 'Acest cont deja exista.')
                        Acest cont deja exista:
                    @else
                        {{ $errors->first() }}
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('register.submit') }}">
                @csrf

                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="label" for="first_name">Prenume</label>
                        <input class="input" id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required>
                    </div>
                    <div>
                        <label class="label" for="last_name">Nume</label>
                        <input class="input" id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required>
                    </div>
                </div>

                <div style="margin-bottom:12px;">
                    <label class="label" for="email">Email</label>
                    <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required>
                </div>

                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                    <div>
                        <label class="label" for="password">Parola</label>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <input class="input" id="password" type="password" name="password" required>
                            <button type="button" class="btn btn-outline btn-sm" data-toggle-password="password">Afiseaza</button>
                        </div>
                    </div>
                    <div>
                        <label class="label" for="password_confirmation">Confirmare parola</label>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <input class="input" id="password_confirmation" type="password" name="password_confirmation" required>
                            <button type="button" class="btn btn-outline btn-sm" data-toggle-password="password_confirmation">Afiseaza</button>
                        </div>
                    </div>
                </div>

                <div style="margin-top:12px;">
                    <label class="label" for="role">Tip cont</label>
                    <select class="input" id="role" name="role" required>
                        <option value="student" @selected(old('role', 'student') === 'student')>Student</option>
                        <option value="profesor" @selected(old('role', 'student') === 'profesor')>Profesor</option>
                    </select>
                </div>

                <div style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary">Creeaza cont</button>
                    <a href="{{ route('login') }}" class="btn btn-secondary">Am deja cont</a>
                </div>
            </form>
        </div>

        <div class="card span-4">
            <h3>Acces</h3>
            <p class="muted">Studentii si profesorii se pot inregistra cu emailuri publice si institutionale.</p>
            <p class="muted">Institutional: {{ implode(', ', config('uniprojectmanager.institutional_domains', [])) ?: 'neconfigurat' }}.</p>
        </div>
    </section>
@endsection
