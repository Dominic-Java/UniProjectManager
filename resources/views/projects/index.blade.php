@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Proiecte</div>
        <h1>Lista proiecte</h1>
        <p>Vizualizeaza proiectele existente sau creeaza unul nou.</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if (session('success'))
                <div class="notice success" style="margin-bottom:12px;">{{ session('success') }}</div>
            @endif

            @if (!$table_exists)
                <div class="notice">
                    Tabela projects nu exista in baza de date. Spune-mi structura (coloane) si o conectam.
                </div>
            @elseif (count($projects) === 0)
                <div class="notice">
                    Nu exista proiecte inregistrate inca.
                </div>
            @else
                <table class="table">
                    <thead>
                    <tr>
                        <th>Titlu</th>
                        <th>Status</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Creat</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($projects as $project)
                        <tr>
                            <td>{{ $project['title'] }}</td>
                            <td>{{ $project['status'] }}</td>
                            <td>{{ $project['start_date'] ?? '-' }}</td>
                            <td>{{ $project['end_date'] ?? '-' }}</td>
                            <td>{{ $project['created_at'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card span-4">
            <h3>Actiuni</h3>
            <p class="muted">Creeaza proiecte si urmareste starea lor.</p>
            <a class="btn btn-primary" href="{{ route('projects.create') }}">Creeaza proiect</a>
        </div>
    </section>
@endsection
