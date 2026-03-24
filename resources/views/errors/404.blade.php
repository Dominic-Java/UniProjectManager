@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Pagina indisponibila</div>
        <h1>Nu am gasit pagina cautata</h1>
        <p>Este posibil ca linkul sa fie expirat, mutat sau introdus incomplet.</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            <div class="notice">Verifica adresa paginii sau revino in sectiunile principale ale platformei.</div>
        </div>

        <div class="card span-4">
            <h3>Navigare rapida</h3>
            <div style="display:flex;flex-direction:column;gap:8px;">
                @auth
                    <a class="btn btn-primary" href="{{ route('dashboard') }}">Dashboard</a>
                @endauth
                @guest
                    <a class="btn btn-primary" href="{{ route('login') }}">Autentificare</a>
                @endguest
                <a class="btn btn-secondary" href="{{ route('landing') }}">Pagina principala</a>
            </div>
        </div>
    </section>
@endsection
