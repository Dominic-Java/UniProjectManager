<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <title>Proiect nou</title>
</head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background:#fff8f1;color:#2b1608;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #f0d7c2;border-radius:12px;">
    <tr>
        <td style="padding:24px;">
            <h1 style="margin:0 0 12px;font-size:24px;">A apărut un proiect nou</h1>

            <p style="margin:0 0 12px;line-height:1.5;">
                <strong>{{ $project->title }}</strong>
            </p>

            <p style="margin:0 0 12px;line-height:1.5;">
                Materie: <strong>{{ $project->domain ?: '-' }}</strong>
            </p>

            <p style="margin:0 0 12px;line-height:1.5;">
                Profesor: <strong>{{ $creator->name }}</strong>
            </p>

            <p style="margin:0 0 18px;line-height:1.5;color:#7c5e46;">
                Intră în platformă ca să vezi detaliile, echipa și livrabilele.
            </p>

            <p style="margin:0;">
                <a href="{{ route('projects.index') }}" style="display:inline-block;background:#ea580c;color:#ffffff;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:700;">
                    Vezi proiectele
                </a>
            </p>
        </td>
    </tr>
</table>
</body>
</html>
