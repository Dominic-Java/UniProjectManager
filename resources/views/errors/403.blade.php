@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Acces restrictionat</div>
        <h1>Nu poti deschide aceasta pagina</h1>
        <p>Aceasta sectiune este disponibila doar pentru anumite roluri sau pentru membrii clasei/proiectului.</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            <div class="notice error" style="margin-bottom:12px;">
                {{ $exception->getMessage() ?: 'Cererea nu poate fi finalizata cu drepturile actuale de acces.' }}
            </div>
            <p class="muted">Daca ai nevoie de acces, contacteaza cadrul didactic sau administratorul platformei.</p>
        </div>

        <div class="card span-4">
            <h3>Ce poti face acum</h3>
            <div style="display:flex;flex-direction:column;gap:8px;">
                @auth
                    <a class="btn btn-primary" href="{{ route('dashboard') }}">Revino in dashboard</a>
                @endauth
                @guest
                    <a class="btn btn-primary" href="{{ route('login') }}">Autentificare</a>
                @endguest
                <a class="btn btn-secondary" href="{{ route('landing') }}">Mergi la pagina principala</a>
            </div>
        </div>
    </section>
@endsection
