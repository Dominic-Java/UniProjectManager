@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Classroom</div>
        <h1>{{ $classroom->name }}</h1>
        <p>{{ $classroom->subject }} - Cod de acces: <strong>{{ $classroom->code }}</strong></p>
        <div style="margin-top:10px;display:flex;align-items:center;gap:10px;">
            @if(!empty($classroom->createdBy?->avatar_url))
                <img src="{{ $classroom->createdBy->avatar_url }}" alt="{{ $classroom->createdBy->name }}" style="width:42px;height:42px;border-radius:999px;object-fit:cover;border:1px solid var(--line);">
            @else
                <div style="width:42px;height:42px;border-radius:999px;border:1px solid var(--line);display:flex;align-items:center;justify-content:center;font-weight:800;">
                    {{ strtoupper(substr($classroom->createdBy?->first_name ?? $classroom->createdBy?->name ?? 'P', 0, 1)) }}
                </div>
            @endif
            <div>
                <div style="font-weight:700;">{{ $classroom->createdBy?->name ?? '-' }}</div>
                <div class="muted">{{ $classroom->subject }}</div>
            </div>
        </div>
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

            <h3>Informatii generale</h3>
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
                    <th>An de studiu</th>
                    <td>{{ $classroom->study_year ? 'Anul ' . $classroom->study_year : '-' }}</td>
                </tr>
                <tr>
                    <th>Cod classroom</th>
                    <td>{{ $classroom->code }}</td>
                </tr>
                <tr>
                    <th>Descriere</th>
                    <td>{{ $classroom->description ?: '-' }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>{{ $classroom->is_active ? 'Activ' : 'Arhivat' }}</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="card span-4">
            <h3>Actiuni</h3>
            <div style="display:flex;flex-direction:column;gap:8px;">
                @if($can_manage && $classroom->is_active)
                    <a class="btn btn-primary" href="{{ route('projects.create', ['classroom' => $classroom->id]) }}">Creeaza proiect in classroom</a>
                @endif
                @if($can_manage)
                    <a class="btn btn-secondary" href="{{ route('catalog.index', ['classroom_id' => $classroom->id]) }}">Catalog si restante</a>
                @endif
                <a class="btn btn-secondary" href="{{ route('classrooms.index') }}">Revino la lista de classroom-uri</a>

                @if($can_manage)
                    @if($classroom->is_active)
                        <form method="POST" action="{{ route('classrooms.archive', $classroom) }}">
                            @csrf
                            @method('PUT')
                            <button class="btn btn-secondary" type="submit" onclick="return confirm('Vrei sa arhivezi acest classroom?')">Arhiveaza classroom-ul</button>
                        </form>
                    @else
                        <div class="notice">Classroom-ul este arhivat. Invitatiile noi nu mai pot fi trimise.</div>
                    @endif

                    <form method="POST" action="{{ route('classrooms.destroy', $classroom) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" type="submit" onclick="return confirm('Vrei sa elimini definitiv acest classroom?')">Sterge classroom-ul</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="card span-6">
            <h3>Membrii classroom-ului</h3>
            @if($classroom->members->count() === 0)
                <div class="notice">Nu exista membri inregistrati in acest classroom.</div>
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
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    @if(!empty($member->avatar_url))
                                        <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" style="width:30px;height:30px;border-radius:999px;object-fit:cover;border:1px solid var(--line);">
                                    @else
                                        <div style="width:30px;height:30px;border-radius:999px;border:1px solid var(--line);display:flex;align-items:center;justify-content:center;font-weight:700;">
                                            {{ strtoupper(substr($member->first_name ?? $member->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <span>{{ $member->name }}</span>
                                </div>
                            </td>
                            <td>{{ $member->email }}</td>
                            <td>{{ $member->pivot->role }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card span-6">
            <h3>Proiecte asociate</h3>
            @if($classroom->projects->count() === 0)
                <div class="notice">Nu exista inca proiecte asociate acestui classroom.</div>
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
                <h3>Invita studenti</h3>
                @if($classroom->is_active)
                    <form method="POST" action="{{ route('classrooms.invitations.send', $classroom) }}">
                        @csrf
                        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                            <div>
                                <label class="label" for="emails">Emailuri studenti</label>
                                <textarea class="input" id="emails" name="emails" rows="4" placeholder="student1@ulbs.ro&#10;student2@ulbs.ro&#10;sau separate prin virgula" required>{{ old('emails', old('email')) }}</textarea>
                            </div>
                            <div>
                                <label class="label" for="message">Mesaj (optional)</label>
                                <input class="input" id="message" type="text" name="message" value="{{ old('message') }}">
                                <p class="muted" style="margin-top:8px;">Poti adauga mai multe adrese in acelasi formular.</p>
                            </div>
                        </div>
                        <button class="btn btn-primary" type="submit" style="margin-top:10px;">Trimite invitatiile</button>
                    </form>
                @else
                    <div class="notice">Classroom-ul este arhivat. Pentru invitatii noi, reactiveaza un classroom activ.</div>
                @endif
            </div>

            <div class="card span-12">
                <h3>Istoric invitatii</h3>
                @if($invitations->count() === 0)
                    <div class="notice">Nu exista invitatii trimise pana acum.</div>
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
                                            <button class="btn btn-danger btn-sm" type="submit">Anuleaza invitatia</button>
                                        </form>
                                    @else
                                        <span class="muted">procesata</span>
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
