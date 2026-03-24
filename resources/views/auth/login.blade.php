@extends('layouts.app')

@push('head')
<style>
    .login-layout {
        align-items: stretch;
    }
    .login-card-main {
        background: linear-gradient(155deg, #fffdf9 0%, #fff4e8 62%, #ffe9d2 100%);
    }
    body[data-theme="dark"] .login-card-main {
        background: linear-gradient(155deg, #0f172a 0%, #1e293b 62%, #334155 100%);
    }
    .login-card-side {
        background: linear-gradient(160deg, #7c2d12 0%, #9a3412 100%);
        color: #ffedd5;
        border-color: rgba(255, 237, 213, 0.28);
    }
    body[data-theme="dark"] .login-card-side {
        background: linear-gradient(145deg, #0b1328 0%, #1e293b 100%);
        color: #dbeafe;
        border-color: #475569;
    }
    .login-side-grid {
        display: grid;
        gap: 10px;
        margin-top: 12px;
    }
    .login-side-item {
        border-radius: 12px;
        border: 1px solid rgba(255, 237, 213, 0.32);
        background: rgba(255, 255, 255, 0.08);
        padding: 12px 14px;
    }
    body[data-theme="dark"] .login-side-item {
        border-color: #475569;
        background: rgba(15, 23, 42, 0.5);
    }
</style>
@endpush

@section('content')
    <section class="hero">
        <div class="pill">Acces in platforma</div>
        <h1>Bine ai revenit</h1>
        <p>Autentifica-te pentru a continua activitatea in clase, proiecte si echipe.</p>
    </section>

    <section class="grid login-layout">
        <div class="card span-7 login-card-main">
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

        <div class="card span-5 login-card-side">
            <h3>Nu ai inca acces?</h3>
            <p style="margin:0;color:inherit;">
                Conturile sunt configurate de administrator. Pentru activare, contacteaza cadrul didactic responsabil.
            </p>
            <div class="login-side-grid">
                <div class="login-side-item">
                    <strong>1. Primesti datele de conectare</strong>
                    <p style="margin:6px 0 0;">Email, rol, ID utilizator si parola initiala.</p>
                </div>
                <div class="login-side-item">
                    <strong>2. Intri in platforma</strong>
                    <p style="margin:6px 0 0;">Selectezi rolul corect si continui in dashboard.</p>
                </div>
                <div class="login-side-item">
                    <strong>3. Iti organizezi activitatea</strong>
                    <p style="margin:6px 0 0;">Accesezi classroom-uri, proiecte, echipe si livrabile.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
