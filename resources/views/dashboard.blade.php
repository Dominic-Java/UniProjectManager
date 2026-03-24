<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .quick-actions-card {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0,0,0,.08);
        }
        .qa-title {
            font-weight: 800;
            letter-spacing: .2px;
        }
        .qa-btn {
            border-radius: 12px;
            padding: .75rem 1.15rem;
            font-weight: 700;
        }
        .qa-btn-primary {
            background: #2f6bff;
            border-color: #2f6bff;
        }
        .qa-btn-primary:hover {
            background: #2559da;
            border-color: #2559da;
        }
        .qa-btn-muted {
            background: #e9ecef;
            border-color: #e9ecef;
            color: #111;
        }
        .qa-note {
            color: #6c757d;
            margin-top: .6rem;
        }
    </style>
</head>
<body class="bg-light">

<div class="container py-4">
    <div class="card quick-actions-card">
        <div class="card-body p-4">
            <h2 class="qa-title h4 mb-3">Actiuni rapide</h2>

            <div class="d-flex flex-wrap gap-3">
                <a href="{{ route('projects.create') ?? '#' }}" class="btn btn-primary qa-btn qa-btn-primary">
                    Creeaza proiect
                </a>

                <a href="{{ route('projects.index') ?? '#' }}" class="btn btn-primary qa-btn qa-btn-primary">
                    Vezi proiectele
                </a>

                <a href="{{ route('teams.index') ?? '#' }}" class="btn btn-primary qa-btn qa-btn-primary">
                    Echipe
                </a>

                <a href="{{ route('deliverables.index') ?? '#' }}" class="btn btn-primary qa-btn qa-btn-primary">
                    Livrabile
                </a>

                <a href="{{ route('settings') ?? '#' }}" class="btn qa-btn qa-btn-muted">
                    Setari
                </a>
            </div>

            <div class="qa-note">
                Linkurile devin active pe masura ce modulele Projects, Teams si Deliverables sunt configurate.
            </div>
        </div>
    </div>
</div>

</body>
</html>
