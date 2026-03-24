@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Echipa</div>
        <h1>{{ $team->name }}</h1>
        <p>Proiect asociat: {{ $team->project?->title ?? '-' }}</p>
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
            @if ($project_locked)
                <div class="notice error" style="margin-bottom:12px;">
                    Proiectul este inchis dupa termen. Nu mai poti invita, adauga sau elimina membri.
                </div>
            @endif

            <table class="table">
                <tbody>
                <tr>
                    <th>Status</th>
                    <td>{{ $team->status }}</td>
                </tr>
                <tr>
                    <th>Creat de</th>
                    <td>{{ $team->createdBy?->name ?? '-' }}</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="card span-4">
            <h3>Actiuni</h3>
            <div style="display:flex;flex-direction:column;gap:8px;">
                @if($can_manage && !$project_locked)
                    <a class="btn btn-secondary" href="{{ route('teams.edit', $team) }}">Modifica echipa</a>
                @endif
                <a class="btn btn-secondary" href="{{ route('teams.index') }}">Inapoi la lista echipelor</a>
            </div>
        </div>

        <div class="card span-6">
            <h3>Membri</h3>
            <ul>
                @foreach($team->members as $member)
                    <li>
                        {{ $member->name }} ({{ $member->pivot->role }})
                        @if($can_manage && !$project_locked)
                            <form method="POST" action="{{ route('teams.members.remove', [$team, $member]) }}" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="margin-left:8px;">Elimina</button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>

            @if($can_manage && !$project_locked)
                <form method="POST" action="{{ route('teams.members.add', $team) }}" style="margin-top:12px;">
                    @csrf
                    <label class="label" for="member_email">Adauga student dupa email</label>
                    <input class="input" id="member_email" type="email" name="email" required>
                    <button type="submit" class="btn btn-secondary" style="margin-top:8px;">Adauga in echipa</button>
                </form>
            @endif
        </div>

        <div class="card span-6">
            <h3>Invitatii</h3>
            @if($invitations->count() === 0)
                <div class="notice">Nu exista invitatii active pentru aceasta echipa.</div>
            @else
                <ul>
                    @foreach($invitations as $inv)
                        <li>
                            {{ $inv->invitedUser?->name ?? '-' }} - {{ $inv->status }}
                            @if($can_manage && !$project_locked && $inv->status === 'pending')
                                <form method="POST" action="{{ route('teams.invitations.cancel', $inv) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-secondary" style="margin-left:8px;">Anuleaza</button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @if($can_manage && !$project_locked)
                <form method="POST" action="{{ route('teams.invitations.send', $team) }}" style="margin-top:12px;">
                    @csrf
                    <label class="label" for="invite_email">Invita student dupa email</label>
                    <input class="input" id="invite_email" type="email" name="email" required>
                    <label class="label" for="message" style="margin-top:8px;">Mesaj</label>
                    <input class="input" id="message" type="text" name="message" value="{{ old('message') }}">
                    <button type="submit" class="btn btn-secondary" style="margin-top:8px;">Trimite invitatia</button>
                </form>
            @endif
        </div>
    </section>
@endsection
