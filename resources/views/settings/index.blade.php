@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Setari</div>
        <h1>Configurare aplicatie</h1>
        <p>Zona pentru setari de cont, securitate si personalizare.</p>
    </section>

    <section class="grid">
        <div class="card span-6">
            <h3>Cont & profil</h3>
            <p class="muted">Vom adauga editarea datelor personale si rolurile.</p>
            <button class="btn btn-secondary" disabled>Editeaza profil</button>
        </div>

        <div class="card span-6">
            <h3>Securitate</h3>
            <p class="muted">Parole, criptare, autentificare cu roluri.</p>
            <button class="btn btn-secondary" disabled>Setari securitate</button>
        </div>

        <div class="card span-12">
            <h3>Ce urmeaza</h3>
            <ul>
                <li>Autentificare (login/register)</li>
                <li>Permisiuni: admin, profesor, student</li>
                <li>Audit si loguri activitate</li>
            </ul>
        </div>
    </section>
@endsection
