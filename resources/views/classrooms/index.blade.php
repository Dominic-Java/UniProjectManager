@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Classroom</div>
        <h1>Classroom-uri pe materii</h1>
        <p>Clasele se creeaza separat, apoi proiectele se adauga in interiorul fiecarei clase.</p>
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

            @if ($classrooms->count() === 0)
                <div class="notice">Nu exista classroom-uri disponibile momentan.</div>
            @else
                <table class="table">
                    <thead>
                    <tr>
                        <th>Clasa</th>
                        <th>Materie</th>
                        <th>Cod</th>
                        <th>Membri</th>
                        <th>Proiecte</th>
                        <th>Actiuni</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($classrooms as $classroom)
                        <tr>
                            <td>{{ $classroom->name }}</td>
                            <td>{{ $classroom->subject }}</td>
                            <td>{{ $classroom->code }}</td>
                            <td>{{ $classroom->members_count }}</td>
                            <td>{{ $classroom->projects_count }}</td>
                            <td>
                                <a class="btn btn-secondary btn-sm" href="{{ route('classrooms.show', $classroom) }}">Deschide</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card span-4">
            <h3>Actiuni rapide</h3>
            @if(auth()->user()?->hasRole('profesor'))
                <p class="muted">Profesorul administreaza doar classroom-urile pe care le preda.</p>
                <a class="btn btn-primary" href="{{ route('classrooms.create') }}">Creeaza classroom</a>
            @else
                <p class="muted">Introdu codul clasei primit de la profesor pentru a intra in classroom.</p>
                <form method="POST" action="{{ route('classrooms.join') }}">
                    @csrf
                    <label class="label" for="classroom_code">Cod classroom</label>
                    <input class="input" id="classroom_code" name="classroom_code" type="text" value="{{ old('classroom_code') }}" placeholder="Ex: CLS-AB12CD34" required>
                    <button class="btn btn-primary" type="submit" style="margin-top:10px;">Intra in classroom</button>
                </form>
            @endif
        </div>

        @if(auth()->user()?->hasRole('student'))
            <div class="card span-12">
                <h3>Invitatii la classroom</h3>
                @if ($invitations->count() === 0)
                    <div class="notice">Nu ai invitatii in asteptare.</div>
                @else
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Clasa</th>
                            <th>Materie</th>
                            <th>Invitat de</th>
                            <th>Mesaj</th>
                            <th>Actiuni</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($invitations as $invitation)
                            <tr>
                                <td>{{ $invitation->classroom?->name ?? '-' }}</td>
                                <td>{{ $invitation->classroom?->subject ?? '-' }}</td>
                                <td>{{ $invitation->invitedBy?->name ?? '-' }}</td>
                                <td>{{ $invitation->message ?: '-' }}</td>
                                <td>
                                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                        <form method="POST" action="{{ route('classrooms.invitations.respond', $invitation) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="accept">
                                            <button class="btn btn-primary btn-sm" type="submit">Accepta</button>
                                        </form>
                                        <form method="POST" action="{{ route('classrooms.invitations.respond', $invitation) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="reject">
                                            <button class="btn btn-secondary btn-sm" type="submit">Respinge</button>
                                        </form>
                                    </div>
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
