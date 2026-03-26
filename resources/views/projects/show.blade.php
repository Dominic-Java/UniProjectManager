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
                    Proiectul este inchis: termenul a fost depasit sau statusul a fost setat pe closed/archived.
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
                    <th>Tip proiect</th>
                    <td>{{ $project->is_retake_project ? 'Restanta (audienta limitata)' : 'Standard' }}</td>
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
                    <a class="btn btn-secondary" href="{{ route('projects.edit', $project) }}">Editeaza proiectul</a>
                @endif
                <a class="btn btn-secondary" href="{{ route('projects.index') }}">Revino la lista proiectelor</a>
            </div>
        </div>

        <div class="card span-4">
            <h3>Echipe</h3>
            <p class="muted">{{ $project->teams->count() }} echipe asociate proiectului</p>
            <ul>
                @foreach($project->teams->take(5) as $team)
                    <li>{{ $team->name }} ({{ $team->status }})</li>
                @endforeach
            </ul>
        </div>

        <div class="card span-4">
            <h3>Milestones</h3>
            <p class="muted">{{ $project->milestones->count() }} etape planificate</p>
            <ul>
                @foreach($project->milestones->take(5) as $ms)
                    <li>{{ $ms->title }} ({{ $ms->due_at ?? '-' }})</li>
                @endforeach
            </ul>
        </div>

        <div class="card span-4">
            <h3>Livrabile</h3>
            <p class="muted">{{ $project->deliverables->count() }} livrabile definite</p>
            <ul>
                @foreach($project->deliverables->take(5) as $del)
                    <li>{{ $del->title }} ({{ $del->due_at ?? '-' }})</li>
                @endforeach
            </ul>
        </div>

        <div class="card span-12">
            <h3>Classwork - Materiale</h3>
            <p class="muted">Adauga aici suport de curs, exemple, cerinte sau alte resurse utile pentru echipe.</p>

            @if($can_upload_materials)
                @if($project->isLocked())
                    <div class="notice error" style="margin-bottom:12px;">
                        Proiectul este inchis dupa deadline. Incarcarea de materiale noi nu mai este disponibila.
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
                            <button type="submit" class="btn btn-primary">Incarca materialul</button>
                        </div>
                    </form>
                @endif
            @endif

            @if($project->materials->count() === 0)
                <div class="notice">Nu exista materiale incarcate inca pentru acest proiect.</div>
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
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Vrei sa stergi acest material?')">Sterge</button>
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
            @if($can_manage)
                @if($project->isLocked())
                    <div class="notice error" style="margin-bottom:12px;">
                        Proiectul este inchis dupa termen. Nu mai poti adauga cerinte noi.
                    </div>
                @else
                    <form method="POST" action="{{ route('projects.requirements.store', $project) }}" style="margin-bottom:14px;">
                        @csrf
                        <div style="display:grid;gap:10px;">
                            <div>
                                <label class="label" for="requirement_title">Titlu cerinta</label>
                                <input class="input" id="requirement_title" type="text" name="requirement_title" value="{{ old('requirement_title') }}" required>
                            </div>
                            <div>
                                <label class="label" for="requirement_description">Mesaj / cerinte pentru studenti</label>
                                <textarea class="input" id="requirement_description" name="requirement_description" rows="4" required>{{ old('requirement_description') }}</textarea>
                            </div>
                            <label style="display:flex;gap:8px;align-items:center;font-size:13px;color:var(--muted);">
                                <input type="checkbox" name="send_requirement_email" value="1">
                                Trimite email automat catre studentii/profesorii care au acces la proiect
                            </label>
                            <div>
                                <button type="submit" class="btn btn-primary btn-sm">Adauga cerinta</button>
                            </div>
                        </div>
                    </form>
                @endif
            @endif

            @if($project->requirements->count() === 0)
                <div class="notice">Nu exista cerinte inregistrate pentru acest proiect.</div>
            @else
                <table class="table">
                    <thead>
                    <tr>
                        <th>Versiune</th>
                        <th>Titlu</th>
                        <th>Descriere</th>
                        <th>Creat de</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($project->requirements->sortByDesc('version') as $req)
                        <tr>
                            <td>v{{ $req->version }}</td>
                            <td>{{ $req->title }}</td>
                            <td>{{ $req->description }}</td>
                            <td>{{ $req->createdBy?->name ?? '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card span-6">
            <h3>Staff</h3>
            @if($project->staff->count() === 0)
                <div class="notice">Nu exista cadre didactice asociate suplimentar acestui proiect.</div>
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
