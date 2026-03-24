@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Resetare parola</div>
        <h1>Recuperare acces</h1>
        <p>Introdu adresa de email, iar noi iti trimitem un link sigur pentru setarea unei parole noi.</p>
    </section>

    <section class="grid">
        <div class="card span-6">
            @if (session('status'))
                <div class="notice" style="margin-bottom:12px;">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="notice error" style="margin-bottom:12px;">
                    Verifica emailul introdus si incearca din nou.
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div style="margin-bottom:12px;">
                    <label class="label" for="email">Email</label>
                    <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required>
                </div>

                <button type="submit" class="btn btn-primary">Trimite link</button>
            </form>
        </div>

        <div class="card span-6">
            <h3>Detalii de securitate</h3>
            <p class="muted">Linkul de resetare este valabil 60 de minute. Daca nu apare in Inbox, verifica si folderul Spam.</p>
            <a href="{{ route('login') }}" class="btn btn-secondary">Revino la autentificare</a>
        </div>
    </section>
@endsection
