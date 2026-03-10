@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Livrabil</div>
        <h1>{{ $deliverable->title }}</h1>
        <p>{{ $deliverable->description }}</p>
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
                    <td>{{ $deliverable->project?->title ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Milestone</th>
                    <td>{{ $deliverable->milestone?->title ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Deadline</th>
                    <td>{{ $deliverable->due_at ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Tip predare</th>
                    <td>{{ $deliverable->submission_type }}</td>
                </tr>
                <tr>
                    <th>Punctaj maxim</th>
                    <td>{{ $deliverable->max_points }}</td>
                </tr>
                <tr>
                    <th>Creat de</th>
                    <td>{{ $deliverable->createdBy?->name ?? '-' }}</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="card span-4">
            <h3>Actiuni</h3>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <a class="btn btn-secondary" href="{{ route('deliverables.edit', $deliverable) }}">Editeaza</a>
                <a class="btn btn-secondary" href="{{ route('deliverables.index') }}">Inapoi</a>
            </div>
        </div>
    </section>
@endsection
