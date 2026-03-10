@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Echipe</div>
        <h1>Lista echipelor</h1>
        <p>Gestioneaza echipele si invitatiile asociate.</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if (session('success'))
                <div class="notice success" style="margin-bottom:12px;">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="notice error" style="margin-bottom:12px;">{{ $errors->first() }}</div>
            @endif

            @if ($teams->count() === 0)
                <div class="notice">Nu exista echipe inregistrate inca.</div>
            @else
                <table class="table">
                    <thead>
                    <tr>
                        <th>Echipa</th>
                        <th>Proiect</th>
                        <th>Status</th>
                        <th>Membri</th>
                        <th>Actiuni</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($teams as $team)
                        <tr>
                            <td>{{ $team->name }}</td>
                            <td>{{ $team->project?->title ?? '-' }}</td>
                            <td>{{ $team->status }}</td>
                            <td>{{ $team->members_count }}</td>
                            <td>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    <a class="btn btn-secondary" href="{{ route('teams.show', $team) }}">Detalii</a>
                                    @if(auth()->user()?->hasRole('admin', 'profesor') || $team->created_by === auth()->id())
                                        <a class="btn btn-secondary" href="{{ route('teams.edit', $team) }}">Editeaza</a>
                                        <form method="POST" action="{{ route('teams.destroy', $team) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Stergi echipa?')">Sterge</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card span-4">
            <h3>Actiuni</h3>
            <p class="muted">Creeaza echipe si gestioneaza membrii.</p>
            <a class="btn btn-primary" href="{{ route('teams.create') }}">Creeaza echipa</a>
        </div>

        <div class="card span-12">
            <h3>Invitatii primite</h3>
            @if ($invitations->count() === 0)
                <div class="notice">Nu ai invitatii in asteptare.</div>
            @else
                <table class="table">
                    <thead>
                    <tr>
                        <th>Echipa</th>
                        <th>Proiect</th>
                        <th>Invitat de</th>
                        <th>Actiune</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($invitations as $inv)
                        <tr>
                            <td>{{ $inv->team?->name ?? '-' }}</td>
                            <td>{{ $inv->team?->project?->title ?? '-' }}</td>
                            <td>{{ $inv->invitedBy?->name ?? '-' }}</td>
                            <td>
                                <form method="POST" action="{{ route('teams.invitations.respond', $inv) }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="action" value="accept">
                                    <button type="submit" class="btn btn-primary">Accepta</button>
                                </form>
                                <form method="POST" action="{{ route('teams.invitations.respond', $inv) }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-secondary">Respinge</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </section>
@endsection
