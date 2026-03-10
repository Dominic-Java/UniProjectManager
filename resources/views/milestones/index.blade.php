@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Milestones</div>
        <h1>Etape proiect</h1>
        <p>Administreaza etapele proiectelor.</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if (session('success'))
                <div class="notice success" style="margin-bottom:12px;">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="notice error" style="margin-bottom:12px;">{{ $errors->first() }}</div>
            @endif

            @if ($milestones->count() === 0)
                <div class="notice">Nu exista milestones inregistrate.</div>
            @else
                <table class="table">
                    <thead>
                    <tr>
                        <th>Titlu</th>
                        <th>Proiect</th>
                        <th>Deadline</th>
                        <th>Pondere</th>
                        <th>Actiuni</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($milestones as $ms)
                        <tr>
                            <td>{{ $ms->title }}</td>
                            <td>{{ $ms->project?->title ?? '-' }}</td>
                            <td>{{ $ms->due_at ?? '-' }}</td>
                            <td>{{ $ms->weight }}</td>
                            <td>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    <a class="btn btn-secondary" href="{{ route('milestones.show', $ms) }}">Detalii</a>
                                    <a class="btn btn-secondary" href="{{ route('milestones.edit', $ms) }}">Editeaza</a>
                                    <form method="POST" action="{{ route('milestones.destroy', $ms) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Stergi milestone-ul?')">Sterge</button>
                                    </form>
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
            <p class="muted">Creeaza o etapa noua.</p>
            <a class="btn btn-primary" href="{{ route('milestones.create') }}">Creeaza milestone</a>
        </div>
    </section>
@endsection
