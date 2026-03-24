@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Resetare parola</div>
        <h1>Seteaza parola noua</h1>
        <p>Completeaza campurile de mai jos pentru a finaliza resetarea contului in conditii de siguranta.</p>
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
                    <button type="submit" class="btn btn-primary">Actualizeaza parola</button>
                </div>
            </form>
        </div>

        <div class="card span-6">
            <h3>Ai nevoie de un link nou?</h3>
            <p class="muted">Daca linkul a expirat, poti solicita imediat unul nou din pagina de recuperare.</p>
            <a href="{{ route('password.request') }}" class="btn btn-secondary">Solicita un nou link</a>
        </div>
    </section>
@endsection
