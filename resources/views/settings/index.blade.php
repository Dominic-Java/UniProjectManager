@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Setari</div>
        <h1>Configurare aplicatie</h1>
        <p>Administrare utilizatori si roluri (profesor/student).</p>
    </section>

    <section class="grid">
        <div class="card span-12">
            <h3>Reguli autorizare cont</h3>
            <p class="muted">Conturile sunt create de administrator, iar rolurile raman doar profesor/student.</p>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-top:12px;">
                <div class="card" style="padding:14px 16px;">
                    <div class="muted">Administratori</div>
                    <div style="font-weight:700;margin-top:6px;">
                        {{ implode(', ', config('uniprojectmanager.admin_emails', [])) ?: 'neconfigurat' }}
                    </div>
                </div>
                <div class="card" style="padding:14px 16px;">
                    <div class="muted">Domenii profesori</div>
                    <div style="font-weight:700;margin-top:6px;">
                        {{ implode(', ', config('uniprojectmanager.professor_domains', [])) ?: 'neconfigurat' }}
                    </div>
                </div>
                <div class="card" style="padding:14px 16px;">
                    <div class="muted">Whitelist profesori</div>
                    <div style="font-weight:700;margin-top:6px;">
                        {{ implode(', ', config('uniprojectmanager.professor_emails', [])) ?: 'neconfigurat' }}
                    </div>
                </div>
                <div class="card" style="padding:14px 16px;">
                    <div class="muted">Domenii institutionale</div>
                    <div style="font-weight:700;margin-top:6px;">
                        {{ implode(', ', config('uniprojectmanager.institutional_domains', [])) ?: 'neconfigurat' }}
                    </div>
                </div>
                <div class="card" style="padding:14px 16px;">
                    <div class="muted">Domenii studenti</div>
                    <div style="font-weight:700;margin-top:6px;">
                        {{ implode(', ', config('uniprojectmanager.student_domains', [])) ?: 'neconfigurat' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card span-12">
            <h3>Creeaza cont</h3>
            <p class="muted">Doar administratorul poate crea conturi pentru studenti si profesori.</p>
            <form method="POST" action="{{ route('settings.users.store') }}" style="margin-top:12px;">
                @csrf
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="label" for="first_name">Prenume</label>
                        <input class="input" id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required>
                    </div>
                    <div>
                        <label class="label" for="last_name">Nume</label>
                        <input class="input" id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required>
                    </div>
                </div>
                <div style="margin-bottom:12px;">
                    <label class="label" for="email">Email</label>
                    <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required>
                </div>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="label" for="password">Parola</label>
                        <input class="input" id="password" type="password" name="password" required>
                    </div>
                    <div>
                        <label class="label" for="password_confirmation">Confirmare parola</label>
                        <input class="input" id="password_confirmation" type="password" name="password_confirmation" required>
                    </div>
                </div>
                <div style="margin-bottom:12px;">
                    <label class="label" for="role">Rol</label>
                    <select class="input" id="role" name="role" required>
                        <option value="student" @selected(old('role', 'student') === 'student')>Student</option>
                        <option value="profesor" @selected(old('role', 'student') === 'profesor')>Profesor</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Creeaza cont</button>
            </form>
        </div>

        <div class="card span-12">
            <h3>Utilizatori</h3>
            @if (session('success'))
                <div class="notice success" style="margin-bottom:12px;">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="notice error" style="margin-bottom:12px;">{{ $errors->first() }}</div>
            @endif

            <table class="table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nume</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Actiuni</th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->member_code ?? '-' }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <form method="POST" action="{{ route('settings.users.update', $user) }}">
                                @csrf
                                @method('PUT')
                                <div style="display:flex;gap:8px;align-items:center;">
                                    <select name="role" class="input" style="width:160px;">
                                        <option value="student" @selected($user->role === 'student')>Student</option>
                                        <option value="profesor" @selected($user->role === 'profesor')>Profesor</option>
                                    </select>
                                    <button type="submit" class="btn btn-secondary">Salveaza</button>
                                </div>
                            </form>
                        </td>
                        <td>
                            @if($user->id === auth()->id())
                                <span class="muted">cont curent</span>
                            @else
                                <form method="POST" action="{{ route('settings.users.destroy', $user) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger"
                                            onclick="return confirm('Stergi utilizatorul?')">Sterge</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
