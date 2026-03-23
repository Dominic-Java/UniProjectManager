@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Classroom</div>
        <h1>{{ $classroom->name }}</h1>
        <p>{{ $classroom->subject }} · Cod acces: <strong>{{ $classroom->code }}</strong></p>
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

            <h3>Despre clasa</h3>
            <table class="table">
                <tbody>
                <tr>
                    <th>Profesor</th>
                    <td>{{ $classroom->createdBy?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Materie</th>
                    <td>{{ $classroom->subject }}</td>
                </tr>
                <tr>
                    <th>Cod classroom</th>
                    <td>{{ $classroom->code }}</td>
                </tr>
                <tr>
                    <th>Descriere</th>
                    <td>{{ $classroom->description ?: '-' }}</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="card span-4">
            <h3>Actiuni</h3>
            <div style="display:flex;flex-direction:column;gap:8px;">
                @if($can_manage)
                    <a class="btn btn-primary" href="{{ route('projects.create', ['classroom' => $classroom->id]) }}">Creeaza proiect in clasa</a>
                @endif
                <a class="btn btn-secondary" href="{{ route('classrooms.index') }}">Inapoi la clase</a>
            </div>
        </div>

        <div class="card span-6">
            <h3>Studenti si profesori in clasa</h3>
            @if($classroom->members->count() === 0)
                <div class="notice">Nu exista membri in aceasta clasa.</div>
            @else
                <table class="table">
                    <thead>
                    <tr>
                        <th>Nume</th>
                        <th>Email</th>
                        <th>Rol</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($classroom->members as $member)
                        <tr>
                            <td>{{ $member->name }}</td>
                            <td>{{ $member->email }}</td>
                            <td>{{ $member->pivot->role }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card span-6">
            <h3>Proiecte in classroom</h3>
            @if($classroom->projects->count() === 0)
                <div class="notice">Nu exista proiecte create inca.</div>
            @else
                <table class="table">
                    <thead>
                    <tr>
                        <th>Titlu</th>
                        <th>Status</th>
                        <th>Deadline</th>
                        <th>Actiuni</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($classroom->projects as $project)
                        <tr>
                            <td>{{ $project->title }}</td>
                            <td>{{ $project->status }}</td>
                            <td>{{ $project->deadline_at ? $project->deadline_at->format('d.m.Y H:i') : '-' }}</td>
                            <td>
                                <a class="btn btn-secondary btn-sm" href="{{ route('projects.show', $project) }}">Detalii</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        @if($can_manage)
            <div class="card span-12">
                <h3>Invita studenti in classroom</h3>
                <form method="POST" action="{{ route('classrooms.invitations.send', $classroom) }}">
                    @csrf
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                        <div>
                            <label class="label" for="email">Email student</label>
                            <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required>
                        </div>
                        <div>
                            <label class="label" for="message">Mesaj (optional)</label>
                            <input class="input" id="message" type="text" name="message" value="{{ old('message') }}">
                        </div>
                    </div>
                    <button class="btn btn-primary" type="submit" style="margin-top:10px;">Trimite invitatie</button>
                </form>
            </div>

            <div class="card span-12">
                <h3>Istoric invitatii</h3>
                @if($invitations->count() === 0)
                    <div class="notice">Nu exista invitatii trimise inca.</div>
                @else
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Student</th>
                            <th>Trimisa de</th>
                            <th>Status</th>
                            <th>Expira</th>
                            <th>Actiuni</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($invitations as $invitation)
                            <tr>
                                <td>{{ $invitation->student?->name ?? '-' }} ({{ $invitation->student?->email ?? '-' }})</td>
                                <td>{{ $invitation->invitedBy?->name ?? '-' }}</td>
                                <td>{{ $invitation->status }}</td>
                                <td>{{ $invitation->expires_at ? $invitation->expires_at->format('d.m.Y H:i') : '-' }}</td>
                                <td>
                                    @if($invitation->status === 'pending')
                                        <form method="POST" action="{{ route('classrooms.invitations.cancel', $invitation) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm" type="submit">Anuleaza</button>
                                        </form>
                                    @else
                                        <span class="muted">finalizata</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endif
    </section>
@endsection
