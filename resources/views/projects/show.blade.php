@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="pill">Proiect</div>
        <h1>{{ $project->title }}</h1>
        <p>{{ $project->description }}</p>
    </section>

    <section class="grid">
        <div class="card span-8">
            @if (session('success'))
                <div class="notice success" style="margin-bottom:12px;">{{ session('success') }}</div>
            @endif
            @if ($project->isLocked())
                <div class="notice error" style="margin-bottom:12px;">
                    Proiect inchis: deadline-ul a fost depasit sau statusul este deja closed/archived.
                </div>
            @endif

            <table class="table">
                <tbody>
                <tr>
                    <th>Cod</th>
                    <td>{{ $project->code ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Materie</th>
                    <td>{{ $project->domain ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Classroom</th>
                    <td>
                        @if($project->classroom)
                            <a href="{{ route('classrooms.show', $project->classroom) }}">
                                {{ $project->classroom->name }} ({{ $project->classroom->code }})
                            </a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>{{ $project->status }}</td>
                </tr>
                <tr>
                    <th>Vizibilitate</th>
                    <td>{{ $project->visibility }}</td>
                </tr>
                <tr>
                    <th>Dimensiune echipa</th>
                    <td>{{ $project->min_team_size }} - {{ $project->max_team_size }}</td>
                </tr>
                <tr>
                    <th>Interval</th>
                    <td>{{ $project->start_date ?? '-' }} - {{ $project->end_date ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Deadline inchidere</th>
                    <td>{{ $project->deadline_at ? $project->deadline_at->format('d.m.Y H:i') : '-' }}</td>
                </tr>
                <tr>
                    <th>Creat de</th>
                    <td>{{ $project->createdBy?->name ?? 'n/a' }}</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="card span-4">
            <h3>Actiuni</h3>
            <div style="display:flex;flex-direction:column;gap:8px;">
                @if($can_manage)
                    <a class="btn btn-secondary" href="{{ route('projects.edit', $project) }}">Editeaza proiect</a>
                @endif
                <a class="btn btn-secondary" href="{{ route('projects.index') }}">Inapoi la lista</a>
            </div>
        </div>

        <div class="card span-4">
            <h3>Echipe</h3>
            <p class="muted">{{ $project->teams->count() }} echipe asociate</p>
            <ul>
                @foreach($project->teams->take(5) as $team)
                    <li>{{ $team->name }} ({{ $team->status }})</li>
                @endforeach
            </ul>
        </div>

        <div class="card span-4">
            <h3>Milestones</h3>
            <p class="muted">{{ $project->milestones->count() }} etape</p>
            <ul>
                @foreach($project->milestones->take(5) as $ms)
                    <li>{{ $ms->title }} ({{ $ms->due_at ?? '-' }})</li>
                @endforeach
            </ul>
        </div>

        <div class="card span-4">
            <h3>Livrabile</h3>
            <p class="muted">{{ $project->deliverables->count() }} livrabile</p>
            <ul>
                @foreach($project->deliverables->take(5) as $del)
                    <li>{{ $del->title }} ({{ $del->due_at ?? '-' }})</li>
                @endforeach
            </ul>
        </div>

        <div class="card span-12">
            <h3>Classwork - Materiale</h3>
            <p class="muted">Aici poti adauga suport de curs, exemple, cerinte sau alte resurse pentru echipe.</p>

            @if($can_upload_materials)
                @if($project->isLocked())
                    <div class="notice error" style="margin-bottom:12px;">
                        Proiectul este inchis dupa deadline. Nu mai poti incarca materiale noi.
                    </div>
                @else
                    <form method="POST" action="{{ route('projects.materials.store', $project) }}" enctype="multipart/form-data" style="margin-bottom:14px;">
                        @csrf
                        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                            <div>
                                <label class="label" for="title">Titlu material</label>
                                <input class="input" id="title" type="text" name="title" value="{{ old('title') }}" required>
                            </div>
                            <div>
                                <label class="label" for="material_file">Fisier material</label>
                                <input class="input" id="material_file" type="file" name="material_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.csv,.zip,.rar,.7z,.png,.jpg,.jpeg,.gif,.webp,.bmp" required>
                            </div>
                        </div>
                        <div style="margin-top:10px;">
                            <button type="submit" class="btn btn-primary">Incarca material</button>
                        </div>
                    </form>
                @endif
            @endif

            @if($project->materials->count() === 0)
                <div class="notice">Nu exista materiale incarcate inca.</div>
            @else
                <table class="table">
                    <thead>
                    <tr>
                        <th>Titlu</th>
                        <th>Fisier</th>
                        <th>Upload de</th>
                        <th>Data</th>
                        <th>Actiuni</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($project->materials->sortByDesc('uploaded_at') as $material)
                        <tr>
                            <td>{{ $material->title }}</td>
                            <td>{{ $material->original_name }}</td>
                            <td>{{ $material->uploadedBy?->name ?? '-' }}</td>
                            <td>{{ $material->uploaded_at?->format('d.m.Y H:i') ?? '-' }}</td>
                            <td>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    <a class="btn btn-secondary btn-sm" href="{{ route('projects.materials.download', $material) }}">Descarca</a>
                                    @if($can_upload_materials && !$project->isLocked())
                                        <form method="POST" action="{{ route('projects.materials.destroy', $material) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Stergi materialul?')">Sterge</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card span-6">
            <h3>Cerinte proiect</h3>
            @if($project->requirements->count() === 0)
                <div class="notice">Nu exista cerinte inregistrate.</div>
            @else
                <ul>
                    @foreach($project->requirements->take(5) as $req)
                        <li>{{ $req->title }} (v{{ $req->version }})</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="card span-6">
            <h3>Staff</h3>
            @if($project->staff->count() === 0)
                <div class="notice">Nu exista profesori asignati.</div>
            @else
                <ul>
                    @foreach($project->staff->take(5) as $staff)
                        <li>{{ $staff->name }} - {{ $staff->pivot->staff_role }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>
@endsection
