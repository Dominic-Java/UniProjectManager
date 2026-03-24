@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Echipe</div>
        <h1>Gestionarea echipelor</h1>
        <p>Urmareste echipele active, invitatiile de colaborare si progresul pe proiecte.</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if (session('success'))
                <div class="notice success" style="margin-bottom:12px;">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="notice error" style="margin-bottom:12px;">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="notice error" style="margin-bottom:12px;">{{ $errors->first() }}</div>
            @endif

            @if ($teams->count() === 0)
                <div class="notice">In acest moment nu exista echipe active.</div>
            @else
                <table class="table">
                    <thead>
                    <tr>
                        <th>Echipa</th>
                        <th>Proiect</th>
                        <th>Statut</th>
                        <th>Membri</th>
                        <th>Actiuni</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($teams as $team)
                        @php($teamLocked = $team->project?->isLocked())
                        <tr>
                            <td>{{ $team->name }}</td>
                            <td>{{ $team->project?->title ?? '-' }}</td>
                            <td>{{ $team->status }}</td>
                            <td>{{ $team->members_count }}</td>
                            <td>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    <a class="btn btn-secondary" href="{{ route('teams.show', $team) }}">Vezi detalii</a>
                                    @if((auth()->user()?->hasRole('profesor') || $team->created_by === auth()->id()) && !$teamLocked)
                                        <a class="btn btn-secondary" href="{{ route('teams.edit', $team) }}">Modifica</a>
                                        <form method="POST" action="{{ route('teams.destroy', $team) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Sigur vrei sa elimini aceasta echipa?')">Elimina</button>
                                        </form>
                                    @elseif($teamLocked)
                                        <span class="muted">Proiect inchis</span>
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
            <h3>Actiuni rapide</h3>
            <p class="muted">Creeaza echipe noi si coordoneaza componenta acestora.</p>
            @if($can_create_team)
                <a class="btn btn-primary" href="{{ route('teams.create') }}">Creeaza echipa noua</a>
            @else
                <div class="notice">Nu exista proiecte deschise pentru crearea unei echipe in acest moment.</div>
            @endif
        </div>

        <div class="card span-12">
            <h3>Invitatii primite</h3>
            @if ($invitations->count() === 0)
                <div class="notice">Nu ai invitatii de echipa in asteptare.</div>
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
                        @php($projectLocked = $inv->team?->project?->isLocked())
                        <tr>
                            <td>{{ $inv->team?->name ?? '-' }}</td>
                            <td>{{ $inv->team?->project?->title ?? '-' }}</td>
                            <td>{{ $inv->invitedBy?->name ?? '-' }}</td>
                            <td>
                                @if($projectLocked)
                                    <span class="muted">Proiect inchis dupa termen</span>
                                @else
                                    <form method="POST" action="{{ route('teams.invitations.respond', $inv) }}" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="action" value="accept">
                                        <button type="submit" class="btn btn-primary">Accepta</button>
                                    </form>
                                    <form method="POST" action="{{ route('teams.invitations.respond', $inv) }}" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-secondary">Refuza</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card span-12">
            <h3>Colaborare eficienta in echipa</h3>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                <div class="card" style="padding:14px;">
                    <strong>Roluri clare</strong>
                    <p class="muted" style="margin:8px 0 0;">Stabileste din timp cine coordoneaza si cine executa.</p>
                </div>
                <div class="card" style="padding:14px;">
                    <strong>Comunicare activa</strong>
                    <p class="muted" style="margin:8px 0 0;">Foloseste invitatiile si statusurile pentru coordonare.</p>
                </div>
                <div class="card" style="padding:14px;">
                    <strong>Respectarea termenelor</strong>
                    <p class="muted" style="margin:8px 0 0;">Sincronizeaza activitatea echipei cu livrabilele proiectului.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
