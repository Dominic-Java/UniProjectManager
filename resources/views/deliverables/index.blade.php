@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Livrabile</div>
        <h1>Urmarire livrabile</h1>
        <p>Urmareste fisierele si etapele incarcate pentru fiecare proiect.</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if (!$table_exists)
                <div class="notice">
                    Tabela deliverables nu exista in baza de date. Trimite-mi structura si o conectam.
                </div>
            @elseif (count($rows) === 0)
                <div class="notice">
                    Nu exista livrabile inregistrate inca.
                </div>
            @else
                <table class="table">
                    <thead>
                    <tr>
                        @foreach($columns as $column)
                            <th>{{ $column }}</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($rows as $row)
                        <tr>
                            @foreach($columns as $column)
                                <td>{{ $row->$column ?? '-' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card span-4">
            <h3>In dezvoltare</h3>
            <p class="muted">Urmatorul pas: incarcare fisiere, deadline-uri, feedback.</p>
            <a class="btn btn-secondary" href="{{ route('projects.index') }}">Vezi proiecte</a>
        </div>
    </section>
@endsection
