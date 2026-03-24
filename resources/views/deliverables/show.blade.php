@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Livrabil</div>
        <h1>{{ $deliverable->title }}</h1>
        <p>{{ $deliverable->description ?: 'Nu a fost adaugata o descriere suplimentara pentru acest livrabil.' }}</p>
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
                    <th>Termen limita</th>
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
                    <a class="btn btn-secondary" href="{{ route('deliverables.edit', $deliverable) }}">Modifica livrabilul</a>
                @elseif(auth()->user()?->hasRole('profesor'))
                    <div class="notice">Proiectul este inchis dupa deadline.</div>
                @endif
                <a class="btn btn-secondary" href="{{ route('deliverables.index') }}">Inapoi la livrabile</a>
            </div>
        </div>

        @if(auth()->user()?->hasRole('student'))
            <div class="card span-12">
                <h3>Predarea ta</h3>

                @if($deliverable->project?->isLocked())
                    <div class="notice error" style="margin-bottom:12px;">
                        Proiectul este inchis dupa termen. Predarea nu mai este disponibila.
                    </div>
                @elseif($deliverable->submission_type === 'link')
                    <div class="notice" style="margin-bottom:12px;">
                        Acest livrabil este configurat doar pentru predare prin link.
                    </div>
                @else
                    <p class="muted">Poti incarca documente, arhive si imagini (.pdf, .docx, .rar, .zip, .png, .jpg, .jpeg etc). Dimensiune maxima: 50 MB.</p>

                    @if($my_submission)
                        <div class="notice success" style="margin-bottom:12px;">
                            Ultima predare inregistrata: {{ $my_submission->submitted_at?->format('d.m.Y H:i') ?? '-' }}
                            <div style="margin-top:8px;">
                                <a class="btn btn-secondary btn-sm" href="{{ route('deliverables.submissions.download', $my_submission) }}">
                                    Descarca fisierul trimis
                                </a>
                                <form method="POST" action="{{ route('deliverables.submissions.cancel', $my_submission) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Anulezi aceasta predare?')">
                                        Retrage predarea
                                    </button>
                                </form>
                            </div>
                            <p class="muted" style="margin-top:8px;">Dupa retragere poti trimite oricand o varianta noua, inainte de termen.</p>
                        </div>
                    @endif

                    @if($my_submission && $my_submission->grade_points !== null)
                        <div class="notice" style="margin-bottom:12px;">
                            <strong>Nota primita:</strong>
                            {{ number_format((float) $my_submission->grade_points, 2) }} / {{ number_format((float) $deliverable->max_points, 2) }}.
                            @if($my_submission->graded_at)
                                Evaluata la {{ $my_submission->graded_at->format('d.m.Y H:i') }}.
                            @endif
                            @if(!empty($my_submission->grade_feedback))
                                <p class="muted" style="margin-top:8px;">Feedback: {{ $my_submission->grade_feedback }}</p>
                            @endif
                        </div>
                    @endif

                    <form method="POST" action="{{ route('deliverables.submit', $deliverable) }}" enctype="multipart/form-data">
                        @csrf
                        <div style="margin-bottom:12px;">
                            <label class="label" for="submission_file">Fisier de predare</label>
                            <input class="input" id="submission_file" type="file" name="submission_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.csv,.zip,.rar,.7z,.png,.jpg,.jpeg,.gif,.webp,.bmp" required>
                        </div>
                        <div style="margin-bottom:12px;">
                            <label class="label" for="notes">Comentariu pentru profesor (optional)</label>
                            <textarea class="input" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Trimite livrabilul</button>
                    </form>
                @endif
            </div>
        @endif

        @if($can_grade_submissions)
            <div class="card span-12">
                <h3>Predari si notare</h3>
                @if($deliverable->submissions->count() === 0)
                    <div class="notice">Nu exista inca fisiere incarcate pentru acest livrabil.</div>
                @else
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Student</th>
                            <th>Fisier</th>
                            <th>Predat la</th>
                            <th>Dimensiune</th>
                            <th>Evaluare</th>
                            <th>Actiuni</th>
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
                                    @if($submission->grade_points !== null)
                                        <strong>{{ number_format((float) $submission->grade_points, 2) }}</strong> / {{ number_format((float) $deliverable->max_points, 2) }}
                                        @if($submission->graded_at)
                                            <div class="muted" style="margin-top:4px;">
                                                {{ $submission->graded_at->format('d.m.Y H:i') }}
                                            </div>
                                        @endif
                                        @if($submission->gradedBy)
                                            <div class="muted" style="margin-top:4px;">
                                                Evaluat de {{ $submission->gradedBy->name }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="muted">Fara nota</span>
                                    @endif
                                </td>
                                <td>
                                    <a class="btn btn-secondary btn-sm" href="{{ route('deliverables.submissions.download', $submission) }}">
                                        Descarca
                                    </a>

                                    <form method="POST" action="{{ route('deliverables.submissions.grade', $submission) }}" style="margin-top:8px;">
                                        @csrf
                                        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-start;">
                                            <input
                                                class="input"
                                                type="number"
                                                name="grade_points"
                                                min="0"
                                                step="0.01"
                                                max="{{ (float) $deliverable->max_points }}"
                                                value="{{ old('grade_points', $submission->grade_points) }}"
                                                placeholder="Nota"
                                                style="width:120px;"
                                                required
                                            >
                                            <textarea
                                                class="input"
                                                name="grade_feedback"
                                                rows="2"
                                                placeholder="Feedback (optional)"
                                                style="min-width:260px;"
                                            >{{ old('grade_feedback', $submission->grade_feedback) }}</textarea>
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                {{ $submission->grade_points !== null ? 'Actualizeaza nota' : 'Salveaza nota' }}
                                            </button>
                                        </div>
                                    </form>
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
