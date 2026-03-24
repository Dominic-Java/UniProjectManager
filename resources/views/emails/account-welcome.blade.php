<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <title>Bun venit pe UniProjectManager</title>
</head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background:#fff8f1;color:#2b1608;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #f0d7c2;border-radius:12px;">
    <tr>
        <td style="padding:24px;">
            <h1 style="margin:0 0 12px;font-size:24px;">Bun venit pe UniProjectManager</h1>

            <p style="margin:0 0 12px;line-height:1.5;">
                Salut, <strong>{{ $user->first_name ?? $user->name }}</strong>!
                Contul tau este pregatit pentru conectare.
            </p>

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:14px 0;border:1px solid #f0d7c2;border-radius:10px;">
                <tr>
                    <td style="padding:12px;">
                        <p style="margin:0 0 8px;"><strong>Email:</strong> {{ $user->email }}</p>
                        <p style="margin:0 0 8px;"><strong>Rol:</strong> {{ $user->role }}</p>
                        <p style="margin:0;"><strong>Cod membru:</strong> {{ $user->member_code ?: '-' }}</p>
                    </td>
                </tr>
            </table>

            @if($createdBy)
                <p style="margin:0 0 14px;line-height:1.5;color:#7c5e46;">
                    Acest cont a fost creat de administratorul <strong>{{ $createdBy->name }}</strong>.
                </p>
            @endif

            <p style="margin:0 0 18px;line-height:1.5;color:#7c5e46;">
                Pentru siguranta, parola nu este trimisa in email. Foloseste butonul de mai jos ca sa iti setezi parola initiala.
            </p>

            <p style="margin:0 0 12px;">
                <a href="{{ $setupUrl }}" style="display:inline-block;background:#ea580c;color:#ffffff;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:700;">
                    Seteaza parola initiala
                </a>
            </p>

            <p style="margin:0 0 16px;line-height:1.5;color:#7c5e46;">
                Linkul este valabil {{ $expiresInMinutes }} de minute. Dupa setarea parolei te poti autentifica in platforma.
            </p>

            <p style="margin:0;">
                <a href="{{ route('login') }}" style="display:inline-block;background:#f5e7dc;color:#7c3f13;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:700;">
                    Mergi la autentificare
                </a>
            </p>
        </td>
    </tr>
</table>
</body>
</html>
