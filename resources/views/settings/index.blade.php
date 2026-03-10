@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Setari</div>
        <h1>Configurare aplicatie</h1>
        <p>Administrare utilizatori si roluri.</p>
    </section>

    <section class="grid">
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
                                        <option value="admin" @selected($user->role === 'admin')>Admin</option>
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
