@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Livrabile</div>
        <h1>Calendarul livrabilelor</h1>
        <p>Profesorii configureaza livrabilele, iar studentii le urmaresc si le predau la timp.</p>
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

            @if ($deliverables->count() === 0)
                <div class="notice">Nu exista livrabile configurate in acest moment.</div>
            @else
                <table class="table">
                    <thead>
                    <tr>
                        <th>Titlu</th>
                        <th>Proiect</th>
                        <th>Milestone</th>
                        <th>Termen</th>
                        <th>Actiuni</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($deliverables as $del)
                        @php($locked = $del->project?->isLocked())
                        <tr>
                            <td>{{ $del->title }}</td>
                            <td>{{ $del->project?->title ?? '-' }}</td>
                            <td>{{ $del->milestone?->title ?? '-' }}</td>
                            <td>{{ $del->due_at ? $del->due_at->format('d.m.Y H:i') : '-' }}</td>
                            <td>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    <a class="btn btn-secondary" href="{{ route('deliverables.show', $del) }}">Vezi detalii</a>
                                    @if(auth()->user()?->hasRole('profesor'))
                                        @if(!$locked)
                                            <a class="btn btn-secondary" href="{{ route('deliverables.edit', $del) }}">Modifica</a>
                                            <form method="POST" action="{{ route('deliverables.destroy', $del) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" onclick="return confirm('Sigur vrei sa elimini acest livrabil?')">Elimina</button>
                                            </form>
                                        @else
                                            <span class="muted">Proiect inchis</span>
                                        @endif
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
            @if(auth()->user()?->hasRole('profesor'))
                <p class="muted">Adauga un livrabil nou pentru proiectele active.</p>
                <a class="btn btn-primary" href="{{ route('deliverables.create') }}">Adauga livrabil</a>
            @else
                <p class="muted">Deschide un livrabil din lista pentru a trimite fisierul tau.</p>
            @endif
        </div>

        <div class="card span-12">
            <h3>Checklist pentru livrabile</h3>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                <div class="card" style="padding:14px;">
                    <strong>Cerinta clara</strong>
                    <p class="muted" style="margin:8px 0 0;">Defineste titlul, descrierea si formatul predarii.</p>
                </div>
                <div class="card" style="padding:14px;">
                    <strong>Termen realist</strong>
                    <p class="muted" style="margin:8px 0 0;">Seteaza termenul in acord cu etapele proiectului.</p>
                </div>
                <div class="card" style="padding:14px;">
                    <strong>Verificare finala</strong>
                    <p class="muted" style="margin:8px 0 0;">Revizuieste predarile studentilor inainte de evaluare.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
