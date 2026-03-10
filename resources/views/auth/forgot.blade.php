@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Resetare parola</div>
        <h1>Ai uitat parola?</h1>
        <p>Trimite emailul si iti oferim un link pentru resetare.</p>
    </section>

    <section class="grid">
        <div class="card span-6">
            @if (session('status'))
                <div class="notice" style="margin-bottom:12px;">{{ session('status') }}</div>
            @endif

            @if (session('reset_link'))
                <div class="notice success" style="margin-bottom:12px;">
                    Link resetare (demo):
                    <div style="margin-top:6px;">
                        <a href="{{ session('reset_link') }}">{{ session('reset_link') }}</a>
                    </div>
                </div>
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
            <h3>Siguranta</h3>
            <p class="muted">Linkul expira dupa 60 de minute. Daca nu ai primit email, verifica spam.</p>
            <a href="{{ route('login') }}" class="btn btn-secondary">Inapoi la login</a>
        </div>
    </section>
@endsection
