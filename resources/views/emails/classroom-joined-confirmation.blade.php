<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <title>Confirmare inscriere classroom</title>
</head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background:#fff8f1;color:#2b1608;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #f0d7c2;border-radius:12px;">
    <tr>
        <td style="padding:24px;">
            <h1 style="margin:0 0 12px;font-size:24px;">Inscriere confirmata in classroom</h1>

            <p style="margin:0 0 12px;line-height:1.5;">
                Salut, <strong>{{ $student->first_name ?? $student->name }}</strong>!
                Ai fost inscris cu succes in classroom-ul de mai jos.
            </p>

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:14px 0;border:1px solid #f0d7c2;border-radius:10px;">
                <tr>
                    <td style="padding:12px;">
                        <p style="margin:0 0 8px;"><strong>Classroom:</strong> {{ $classroom->name }}</p>
                        <p style="margin:0 0 8px;"><strong>Materie:</strong> {{ $classroom->subject }}</p>
                        <p style="margin:0 0 8px;"><strong>Cod clasa:</strong> {{ $classroom->code }}</p>
                        <p style="margin:0 0 8px;"><strong>Profesor:</strong> {{ $classroom->createdBy?->name ?? '-' }}</p>
                        <p style="margin:0 0 8px;"><strong>Metoda inscriere:</strong> {{ $joinedVia === 'invitation' ? 'acceptare invitatie' : 'cod classroom' }}</p>
                        <p style="margin:0;"><strong>Confirmat la:</strong> {{ $openedAt }}</p>
                    </td>
                </tr>
            </table>

            <p style="margin:0 0 18px;line-height:1.5;color:#7c5e46;">
                Poti intra acum in platforma pentru a vedea proiectele si materialele din acest classroom.
            </p>

            <p style="margin:0;">
                <a href="{{ route('classrooms.show', $classroom) }}" style="display:inline-block;background:#ea580c;color:#ffffff;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:700;">
                    Deschide classroom
                </a>
            </p>
        </td>
    </tr>
</table>
</body>
</html>
