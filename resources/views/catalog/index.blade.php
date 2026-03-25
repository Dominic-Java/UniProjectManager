@extends('layouts.app')

@section('content')
    @if($mode === 'staff')
        <section class="hero">
            <div class="pill">Catalog</div>
            <h1>Situatie scolara pe materii</h1>
            <p>Adauga notele studentilor, identifica restantele si trimite detalii de recuperare.</p>
        </section>

        <section class="grid">
            <div class="card span-12">
                @if (session('success'))
                    <div class="notice success" style="margin-bottom:12px;">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="notice error" style="margin-bottom:12px;">{{ $errors->first() }}</div>
                @endif

                @if($classrooms->count() === 0)
                    <div class="notice">Nu exista classroom-uri pe care sa le poti administra in catalog.</div>
                @else
                    <form method="GET" action="{{ route('catalog.index') }}" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                        <div style="min-width:320px;flex:1;">
                            <label class="label" for="classroom_id">Classroom</label>
                            <select class="input" id="classroom_id" name="classroom_id">
                                @foreach($classrooms as $classroom)
                                    <option value="{{ $classroom->id }}" @selected($selected_classroom && $selected_classroom->id === $classroom->id)>
                                        {{ $classroom->name }} - {{ $classroom->subject }} ({{ $classroom->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-primary" type="submit">Afiseaza catalogul</button>
                    </form>
                @endif
            </div>

            @if($selected_classroom)
                <div class="card span-12">
                    <h3>
                        Catalog: {{ $selected_classroom->subject }} - {{ $selected_classroom->name }}
                    </h3>
                    <p class="muted">
                        Profesor coordonator: {{ $selected_classroom->createdBy?->name ?? '-' }}
                    </p>

                    @if($student_rows->count() === 0)
                        <div class="notice">Nu exista studenti inscrisi in acest classroom.</div>
                    @else
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Student</th>
                                <th>Email</th>
                                <th>Nota</th>
                                <th>Status</th>
                                <th>Evaluat de</th>
                                <th>Feedback</th>
                                <th>Actiune</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($student_rows as $row)
                                @php($student = $row['student'])
                                <tr>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            @if(!empty($student->avatar_url))
                                                <img src="{{ $student->avatar_url }}" alt="{{ $student->name }}" style="width:34px;height:34px;border-radius:999px;object-fit:cover;border:1px solid var(--line);">
                                            @else
                                                <div style="width:34px;height:34px;border-radius:999px;border:1px solid var(--line);display:flex;align-items:center;justify-content:center;font-weight:700;">
                                                    {{ strtoupper(substr($student->first_name ?? $student->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <span>{{ $student->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $student->email }}</td>
                                    <td>{{ $row['grade_value'] !== null ? number_format((float) $row['grade_value'], 2) : '-' }}</td>
                                    <td>
                                        @if($row['grade_value'] === null)
                                            <span class="muted">Neevaluat</span>
                                        @elseif($row['is_failing'])
                                            <span style="color:#b91c1c;font-weight:700;">Restanta</span>
                                        @else
                                            <span style="color:#166534;font-weight:700;">Promovat</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $row['graded_by'] ?? '-' }}
                                        @if($row['graded_at'])
                                            <div class="muted">{{ $row['graded_at']->format('d.m.Y H:i') }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $row['feedback'] ?: '-' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('catalog.grades.store', $selected_classroom) }}" style="display:grid;gap:8px;">
                                            @csrf
                                            <input type="hidden" name="student_user_id" value="{{ $student->id }}">
                                            <input class="input" type="number" name="grade_value" min="1" max="10" step="0.01" value="{{ old('student_user_id') == $student->id ? old('grade_value') : $row['grade_value'] }}" placeholder="1-10" required>
                                            <textarea class="input" name="feedback" rows="2" placeholder="Feedback optional">{{ old('student_user_id') == $student->id ? old('feedback') : $row['feedback'] }}</textarea>
                                            <label style="display:flex;gap:8px;align-items:center;font-size:12px;color:var(--muted);">
                                                <input type="checkbox" name="send_warning_mail" value="1">
                                                Trimite avertizare email daca nota este sub 5
                                            </label>
                                            <button class="btn btn-primary btn-sm" type="submit">
                                                {{ $row['grade_value'] === null ? 'Salveaza nota' : 'Actualizeaza nota' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                <div class="card span-12">
                    <h3>Trimite email catre studentii restantieri</h3>
                    @if($failing_students->count() === 0)
                        <div class="notice">Nu exista studenti restanti in acest classroom.</div>
                    @else
                        <form method="POST" action="{{ route('catalog.retake-emails.send', $selected_classroom) }}">
                            @csrf

                            <table class="table" style="margin-bottom:12px;">
                                <thead>
                                <tr>
                                    <th>Selecteaza</th>
                                    <th>Student</th>
                                    <th>Email</th>
                                    <th>Nota</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($failing_students as $row)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="student_ids[]" value="{{ $row['student']->id }}">
                                        </td>
                                        <td>{{ $row['student']->name }}</td>
                                        <td>{{ $row['student']->email }}</td>
                                        <td>{{ number_format((float) $row['grade_value'], 2) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                            <label class="label" for="details">Mesaj catre studenti (detalii restanta)</label>
                            <textarea class="input" id="details" name="details" rows="5" placeholder="Ex: Proiect nou pentru restanta, cerinte suplimentare, data sustinerii..." required>{{ old('details') }}</textarea>

                            <div style="margin-top:10px;">
                                <button class="btn btn-primary" type="submit">Trimite email catre studentii selectati</button>
                            </div>
                        </form>
                    @endif
                </div>
            @endif
        </section>
    @else
        <section class="hero">
            <div class="pill">Situatie scolara</div>
            <h1>Catalogul tau</h1>
            <p>Vezi materiile, notele si profesorii pentru fiecare disciplina la care esti inscris.</p>
        </section>

        <section class="grid">
            <div class="card span-12">
                @if (session('success'))
                    <div class="notice success" style="margin-bottom:12px;">{{ session('success') }}</div>
                @endif

                @if($records->count() === 0)
                    <div class="notice">Nu ai inca discipline in catalog. Verifica daca esti inscris in classroom-uri.</div>
                @else
                    @if($failing_count > 0)
                        <div class="notice error" style="margin-bottom:12px;">
                            Ai {{ $failing_count }} {{ $failing_count === 1 ? 'restanta' : 'restante' }} active.
                        </div>
                    @endif

                    <table class="table">
                        <thead>
                        <tr>
                            <th>Materie</th>
                            <th>Classroom</th>
                            <th>Profesor</th>
                            <th>Nota</th>
                            <th>Status</th>
                            <th>Feedback</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($records as $record)
                            <tr>
                                <td>{{ $record['subject'] }}</td>
                                <td>{{ $record['classroom_name'] }}</td>
                                <td>{{ $record['professor_name'] }}</td>
                                <td>
                                    @if($record['grade_value'] === null)
                                        -
                                    @else
                                        {{ number_format((float) $record['grade_value'], 2) }}
                                    @endif
                                </td>
                                <td>
                                    @if($record['grade_value'] === null)
                                        <span class="muted">Neevaluat</span>
                                    @elseif($record['is_failing'])
                                        <span style="color:#b91c1c;font-weight:700;">Restanta</span>
                                    @else
                                        <span style="color:#166534;font-weight:700;">Promovat</span>
                                    @endif
                                </td>
                                <td>{{ $record['feedback'] ?: '-' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </section>
    @endif
@endsection

