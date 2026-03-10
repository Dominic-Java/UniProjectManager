@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Livrabile</div>
        <h1>Lista livrabile</h1>
        <p>Administreaza livrabilele si deadline-urile.</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if (session('success'))
                <div class="notice success" style="margin-bottom:12px;">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="notice error" style="margin-bottom:12px;">{{ $errors->first() }}</div>
            @endif

            @if ($deliverables->count() === 0)
                <div class="notice">Nu exista livrabile inregistrate.</div>
            @else
                <table class="table">
                    <thead>
                    <tr>
                        <th>Titlu</th>
                        <th>Proiect</th>
                        <th>Milestone</th>
                        <th>Deadline</th>
                        <th>Actiuni</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($deliverables as $del)
                        <tr>
                            <td>{{ $del->title }}</td>
                            <td>{{ $del->project?->title ?? '-' }}</td>
                            <td>{{ $del->milestone?->title ?? '-' }}</td>
                            <td>{{ $del->due_at ?? '-' }}</td>
                            <td>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    <a class="btn btn-secondary" href="{{ route('deliverables.show', $del) }}">Detalii</a>
                                    <a class="btn btn-secondary" href="{{ route('deliverables.edit', $del) }}">Editeaza</a>
                                    <form method="POST" action="{{ route('deliverables.destroy', $del) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Stergi livrabilul?')">Sterge</button>
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
            <p class="muted">Creeaza un livrabil nou pentru proiect.</p>
            <a class="btn btn-primary" href="{{ route('deliverables.create') }}">Creeaza livrabil</a>
        </div>
    </section>
@endsection
