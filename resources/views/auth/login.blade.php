@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Acces platforma</div>
        <h1>Autentificare</h1>
        <p>Intra in contul tau pentru a gestiona proiectele studentesti.</p>
    </section>

    <section class="grid">
        <div class="card span-6">
            @if (session('success'))
                <div class="notice success" style="margin-bottom:12px;">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="notice error" style="margin-bottom:12px;">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="notice error" style="margin-bottom:12px;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}">
                @csrf

                <div style="margin-bottom:12px;">
                    <label class="label" for="email">Email</label>
                    <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>

                <div style="margin-bottom:12px;">
                    <label class="label" for="password">Parola</label>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <input class="input" id="password" type="password" name="password" required>
                        <button type="button" class="btn btn-outline btn-sm" data-toggle-password="password">Afiseaza</button>
                    </div>
                </div>

                <div style="margin-bottom:12px;">
                    <label class="label" for="role">Tip cont</label>
                    <select class="input" id="role" name="role" required>
                        <option value="student" @selected(old('role', 'student') === 'student')>Student</option>
                        <option value="profesor" @selected(old('role', 'student') === 'profesor')>Profesor</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Intra in cont</button>
            </form>
            <div style="margin-top:12px;">
                <a class="muted" href="{{ route('password.request') }}">Ai uitat parola?</a>
            </div>
        </div>

        <div class="card span-6">
            <h3>Cont nou?</h3>
            <p class="muted">Daca nu ai cont, creeaza unul acum.</p>
            <a href="{{ route('register') }}" class="btn btn-secondary">Mergi la inregistrare</a>
        </div>
    </section>
@endsection
