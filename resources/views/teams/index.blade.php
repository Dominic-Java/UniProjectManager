@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Echipe</div>
        <h1>Gestionare echipe</h1>
        <p>Vizualizeaza echipele existente si membrii lor.</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if (!$table_exists)
                <div class="notice">
                    Tabela teams nu exista in baza de date. Trimite-mi structura si o conectam.
                </div>
            @elseif (count($rows) === 0)
                <div class="notice">
                    Nu exista echipe inregistrate inca.
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
            <p class="muted">Urmatorul pas: creare echipe, adaugare studenti, roluri.</p>
            <a class="btn btn-secondary" href="{{ route('projects.index') }}">Vezi proiecte</a>
        </div>
    </section>
@endsection
