<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <title>Cerinte noi in proiect</title>
</head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background:#fff8f1;color:#2b1608;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #f0d7c2;border-radius:12px;">
    <tr>
        <td style="padding:24px;">
            <h1 style="margin:0 0 12px;font-size:24px;">Cerinte noi in proiect</h1>

            <p style="margin:0 0 12px;line-height:1.5;">
                Profesorul a adaugat o cerinta noua in proiectul <strong>{{ $project->title }}</strong>.
            </p>

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:14px 0;border:1px solid #f0d7c2;border-radius:10px;">
                <tr>
                    <td style="padding:12px;">
                        <p style="margin:0 0 8px;"><strong>Titlu cerinta:</strong> {{ $requirement->title }}</p>
                        <p style="margin:0 0 8px;"><strong>Versiune:</strong> v{{ $requirement->version }}</p>
                        <p style="margin:0 0 8px;"><strong>Adaugata de:</strong> {{ $author->name }}</p>
                        <p style="margin:0;"><strong>Data:</strong> {{ optional($requirement->created_at)->format('d.m.Y H:i') ?: now()->format('d.m.Y H:i') }}</p>
                    </td>
                </tr>
            </table>

            <div style="margin:0 0 16px;padding:12px;border-radius:10px;background:#fff3e8;border:1px solid #f0d7c2;line-height:1.5;">
                <strong>Detalii cerinta:</strong><br>
                {!! nl2br(e($requirement->description)) !!}
            </div>

            <p style="margin:0;">
                <a href="{{ route('projects.show', $project) }}" style="display:inline-block;background:#ea580c;color:#ffffff;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:700;">
                    Deschide proiect
                </a>
            </p>
        </td>
    </tr>
</table>
</body>
</html>
