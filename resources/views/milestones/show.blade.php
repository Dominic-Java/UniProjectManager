@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Milestone</div>
        <h1>{{ $milestone->title }}</h1>
        <p>{{ $milestone->description }}</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if (session('success'))
                <div class="notice success" style="margin-bottom:12px;">{{ session('success') }}</div>
            @endif

            <table class="table">
                <tbody>
                <tr>
                    <th>Proiect</th>
                    <td>{{ $milestone->project?->title ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Deadline</th>
                    <td>{{ $milestone->due_at ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Pondere</th>
                    <td>{{ $milestone->weight }}</td>
                </tr>
                <tr>
                    <th>Creat de</th>
                    <td>{{ $milestone->createdBy?->name ?? '-' }}</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="card span-4">
            <h3>Actiuni</h3>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <a class="btn btn-secondary" href="{{ route('milestones.edit', $milestone) }}">Editeaza</a>
                <a class="btn btn-secondary" href="{{ route('milestones.index') }}">Inapoi</a>
            </div>
        </div>

        <div class="card span-12">
            <h3>Livrabile asociate</h3>
            @if ($milestone->deliverables->count() === 0)
                <div class="notice">Nu exista livrabile asociate.</div>
            @else
                <ul>
                    @foreach($milestone->deliverables as $del)
                        <li>{{ $del->title }} ({{ $del->due_at ?? '-' }})</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>
@endsection
