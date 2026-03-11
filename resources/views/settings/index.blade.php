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
            <p class="muted">Profesorii sunt acceptati pe baza domeniilor/whitelist configurate in `.env`.</p>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-top:12px;">
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
                    <th>Nume</th>
                    <th>Email</th>
                    <th>Rol</th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
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
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
