@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Etape</div>
        <h1>Etapele proiectelor</h1>
        <p>Defineste si urmareste reperele intermediare pentru fiecare proiect.</p>
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

            @if ($milestones->count() === 0)
                <div class="notice">Nu exista etape configurate in acest moment.</div>
            @else
                <table class="table">
                    <thead>
                    <tr>
                        <th>Titlu</th>
                        <th>Proiect</th>
                        <th>Termen</th>
                        <th>Pondere</th>
                        <th>Actiuni</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($milestones as $ms)
                        @php($locked = $ms->project?->isLocked())
                        <tr>
                            <td>{{ $ms->title }}</td>
                            <td>{{ $ms->project?->title ?? '-' }}</td>
                            <td>{{ $ms->due_at ? $ms->due_at->format('d.m.Y H:i') : '-' }}</td>
                            <td>{{ $ms->weight }}</td>
                            <td>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    <a class="btn btn-secondary" href="{{ route('milestones.show', $ms) }}">Vezi detalii</a>
                                    @if(auth()->user()?->hasRole('profesor'))
                                        @if(!$locked)
                                            <a class="btn btn-secondary" href="{{ route('milestones.edit', $ms) }}">Modifica</a>
                                            <form method="POST" action="{{ route('milestones.destroy', $ms) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" onclick="return confirm('Sigur vrei sa elimini aceasta etapa?')">Elimina</button>
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
                <p class="muted">Adauga o etapa noua pentru a structura mai clar proiectul.</p>
                <a class="btn btn-primary" href="{{ route('milestones.create') }}">Adauga etapa noua</a>
            @else
                <p class="muted">Aceasta sectiune este gestionata de cadrele didactice.</p>
            @endif
        </div>

        <div class="card span-12">
            <h3>Ritm de lucru recomandat</h3>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                <div class="card" style="padding:14px;">
                    <strong>Planificare initiala</strong>
                    <p class="muted" style="margin:8px 0 0;">Imparte proiectul in etape cu rezultate masurabile.</p>
                </div>
                <div class="card" style="padding:14px;">
                    <strong>Revizuire periodica</strong>
                    <p class="muted" style="margin:8px 0 0;">Actualizeaza termenele cand apar schimbari reale.</p>
                </div>
                <div class="card" style="padding:14px;">
                    <strong>Aliniere cu livrabilele</strong>
                    <p class="muted" style="margin:8px 0 0;">Pastreaza coerenta intre etape si predari.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
