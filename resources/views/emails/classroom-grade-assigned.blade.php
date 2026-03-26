<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <title>Nota in catalog</title>
</head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background:#fff8f1;color:#2b1608;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #f0d7c2;border-radius:12px;">
    <tr>
        <td style="padding:24px;">
            <h1 style="margin:0 0 12px;font-size:24px;">{{ $isUpdate ? 'Nota ta a fost actualizata' : 'Ai primit o nota noua' }}</h1>

            <p style="margin:0 0 12px;line-height:1.5;">
                Salut, <strong>{{ $grade->student?->first_name ?? $grade->student?->name ?? 'student' }}</strong>!
                {{ $isUpdate ? 'Nota din catalog a fost actualizata.' : 'A fost adaugata o nota noua in catalog.' }}
            </p>

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:14px 0;border:1px solid #f0d7c2;border-radius:10px;">
                <tr>
                    <td style="padding:12px;">
                        <p style="margin:0 0 8px;"><strong>Materie:</strong> {{ $grade->classroom?->subject ?? '-' }}</p>
                        <p style="margin:0 0 8px;"><strong>Clasa:</strong> {{ $grade->classroom?->name ?? '-' }}</p>
                        <p style="margin:0 0 8px;"><strong>Nota:</strong> {{ number_format((float) $grade->grade_value, 2) }}</p>
                        <p style="margin:0;"><strong>Profesor:</strong> {{ $gradedBy->name }}</p>
                    </td>
                </tr>
            </table>

            @if(!empty($grade->feedback))
                <div style="margin:0 0 16px;padding:12px;border-radius:10px;background:#fff3e8;border:1px solid #f0d7c2;line-height:1.5;">
                    <strong>Feedback:</strong><br>
                    {{ $grade->feedback }}
                </div>
            @endif

            <p style="margin:0;">
                <a href="{{ route('catalog.index') }}" style="display:inline-block;background:#ea580c;color:#ffffff;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:700;">
                    Vezi catalogul
                </a>
            </p>
        </td>
    </tr>
</table>
</body>
</html>

