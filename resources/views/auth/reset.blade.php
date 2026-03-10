@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Resetare parola</div>
        <h1>Seteaza o parola noua</h1>
        <p>Completeaza formularul pentru a finaliza resetarea.</p>
    </section>

    <section class="grid">
        <div class="card span-6">
            @if ($errors->any())
                <div class="notice error" style="margin-bottom:12px;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ old('token', $token) }}">

                <div style="margin-bottom:12px;">
                    <label class="label" for="email">Email</label>
                    <input class="input" id="email" type="email" name="email" value="{{ old('email', $email) }}" required>
                </div>

                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                    <div>
                        <label class="label" for="password">Parola noua</label>
                        <input class="input" id="password" type="password" name="password" required>
                    </div>
                    <div>
                        <label class="label" for="password_confirmation">Confirmare parola</label>
                        <input class="input" id="password_confirmation" type="password" name="password_confirmation" required>
                    </div>
                </div>

                <div style="margin-top:16px;">
                    <button type="submit" class="btn btn-primary">Reseteaza parola</button>
                </div>
            </form>
        </div>

        <div class="card span-6">
            <h3>Atentie</h3>
            <p class="muted">Linkul expira dupa 60 de minute. Daca ai probleme, cere un link nou.</p>
            <a href="{{ route('password.request') }}" class="btn btn-secondary">Solicita alt link</a>
        </div>
    </section>
@endsection
