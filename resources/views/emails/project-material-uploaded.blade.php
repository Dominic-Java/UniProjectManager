<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <title>Material nou in proiect</title>
</head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background:#fff8f1;color:#2b1608;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #f0d7c2;border-radius:12px;">
    <tr>
        <td style="padding:24px;">
            <h1 style="margin:0 0 12px;font-size:24px;">Material nou in proiect</h1>

            <p style="margin:0 0 12px;line-height:1.5;">
                A fost adaugat un material nou in proiectul <strong>{{ $project->title }}</strong>.
            </p>

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:14px 0;border:1px solid #f0d7c2;border-radius:10px;">
                <tr>
                    <td style="padding:12px;">
                        <p style="margin:0 0 8px;"><strong>Titlu material:</strong> {{ $material->title }}</p>
                        <p style="margin:0 0 8px;"><strong>Fisier:</strong> {{ $material->original_name }}</p>
                        <p style="margin:0 0 8px;"><strong>Incarcat de:</strong> {{ $uploadedBy->name }}</p>
                        <p style="margin:0;"><strong>La:</strong> {{ optional($material->uploaded_at)->format('d.m.Y H:i') ?: now()->format('d.m.Y H:i') }}</p>
                    </td>
                </tr>
            </table>

            <p style="margin:0 0 18px;line-height:1.5;color:#7c5e46;">
                Intra in proiect pentru a descarca materialul si pentru a urmari actualizarile.
            </p>

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
