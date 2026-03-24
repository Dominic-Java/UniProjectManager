<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <title>Resetare parola</title>
</head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background:#fff8f1;color:#2b1608;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #f0d7c2;border-radius:12px;">
    <tr>
        <td style="padding:24px;">
            <h1 style="margin:0 0 12px;font-size:24px;">Salut, {{ $user->first_name ?? $user->name }}!</h1>
            <p style="margin:0 0 14px;line-height:1.5;">
                Ai cerut resetarea parolei pentru contul tau UniProjectManager.
                Apasa butonul de mai jos pentru a seta o parola noua.
            </p>

            <p style="margin:20px 0;">
                <a href="{{ $resetUrl }}" style="display:inline-block;background:#ea580c;color:#ffffff;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:700;">
                    Reseteaza parola
                </a>
            </p>

            <p style="margin:0 0 10px;line-height:1.5;">
                Linkul expira in {{ $expiresInMinutes }} de minute.
                Daca nu ai facut tu aceasta solicitare, poti ignora acest mesaj.
            </p>

            <p style="margin:12px 0 0;line-height:1.5;color:#7c5e46;font-size:13px;">
                Daca butonul nu functioneaza, copiaza acest link in browser:<br>
                <a href="{{ $resetUrl }}" style="color:#9a3412;">{{ $resetUrl }}</a>
            </p>
        </td>
    </tr>
</table>
</body>
</html>
