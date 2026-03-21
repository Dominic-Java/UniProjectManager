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
            @if (session('error'))
                <div class="notice error" style="margin-bottom:12px;">{{ session('error') }}</div>
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
                    <td>{{ $deliverable->due_at ? $deliverable->due_at->format('d.m.Y H:i') : '-' }}</td>
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
                @if(auth()->user()?->hasRole('profesor') && !$deliverable->project?->isLocked())
                    <a class="btn btn-secondary" href="{{ route('deliverables.edit', $deliverable) }}">Editeaza</a>
                @elseif(auth()->user()?->hasRole('profesor'))
                    <div class="notice">Proiectul este inchis dupa deadline.</div>
                @endif
                <a class="btn btn-secondary" href="{{ route('deliverables.index') }}">Inapoi</a>
            </div>
        </div>

        @if(auth()->user()?->hasRole('student'))
            <div class="card span-12">
                <h3>Predare livrabil</h3>

                @if($deliverable->project?->isLocked())
                    <div class="notice error" style="margin-bottom:12px;">
                        Proiectul este inchis dupa deadline. Predarea de fisiere nu mai este disponibila.
                    </div>
                @elseif($deliverable->submission_type === 'link')
                    <div class="notice" style="margin-bottom:12px;">
                        Acest livrabil este configurat doar pentru predare prin link.
                    </div>
                @else
                    <p class="muted">Poti incarca documente, arhive si imagini (.pdf, .docx, .rar, .zip, .png, .jpg, .jpeg etc). Limita: 50MB.</p>

                    @if($my_submission)
                        <div class="notice success" style="margin-bottom:12px;">
                            Ultima predare: {{ $my_submission->submitted_at?->format('d.m.Y H:i') ?? '-' }}
                            <div style="margin-top:8px;">
                                <a class="btn btn-secondary btn-sm" href="{{ route('deliverables.submissions.download', $my_submission) }}">
                                    Descarca fisierul incarcat
                                </a>
                                <form method="POST" action="{{ route('deliverables.submissions.cancel', $my_submission) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Anulezi aceasta predare?')">
                                        Anuleaza trimiterea
                                    </button>
                                </form>
                            </div>
                            <p class="muted" style="margin-top:8px;">Dupa anulare poti incarca oricand o varianta noua, inainte de deadline.</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('deliverables.submit', $deliverable) }}" enctype="multipart/form-data">
                        @csrf
                        <div style="margin-bottom:12px;">
                            <label class="label" for="submission_file">Fisier livrabil</label>
                            <input class="input" id="submission_file" type="file" name="submission_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.csv,.zip,.rar,.7z,.png,.jpg,.jpeg,.gif,.webp,.bmp" required>
                        </div>
                        <div style="margin-bottom:12px;">
                            <label class="label" for="notes">Comentariu (optional)</label>
                            <textarea class="input" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Incarca livrabil</button>
                    </form>
                @endif
            </div>
        @endif

        @if(auth()->user()?->hasRole('profesor'))
            <div class="card span-12">
                <h3>Predari studenti</h3>
                @if($deliverable->submissions->count() === 0)
                    <div class="notice">Nu exista inca fisiere incarcate pentru acest livrabil.</div>
                @else
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Student</th>
                            <th>Fisier</th>
                            <th>Trimis la</th>
                            <th>Dimensiune</th>
                            <th>Actiune</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($deliverable->submissions->sortByDesc('submitted_at') as $submission)
                            <tr>
                                <td>{{ $submission->student?->name ?? '-' }}</td>
                                <td>{{ $submission->original_name }}</td>
                                <td>{{ $submission->submitted_at?->format('d.m.Y H:i') ?? '-' }}</td>
                                <td>{{ number_format(($submission->file_size_bytes ?? 0) / 1024, 1) }} KB</td>
                                <td>
                                    <a class="btn btn-secondary btn-sm" href="{{ route('deliverables.submissions.download', $submission) }}">
                                        Download
                                    </a>
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
