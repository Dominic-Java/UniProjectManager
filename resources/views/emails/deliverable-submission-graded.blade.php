<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <title>Nota pentru livrabil</title>
</head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background:#fff8f1;color:#2b1608;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #f0d7c2;border-radius:12px;">
    <tr>
        <td style="padding:24px;">
            <h1 style="margin:0 0 12px;font-size:24px;">{{ $isUpdate ? 'Nota ta a fost actualizata' : 'Ai primit o nota noua' }}</h1>

            <p style="margin:0 0 12px;line-height:1.5;">
                Salut, <strong>{{ $submission->student?->first_name ?? $submission->student?->name ?? 'student' }}</strong>!
                {{ $isUpdate ? 'Evaluarea livrabilului tau a fost actualizata.' : 'Livrabilul tau a fost evaluat.' }}
            </p>

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:14px 0;border:1px solid #f0d7c2;border-radius:10px;">
                <tr>
                    <td style="padding:12px;">
                        <p style="margin:0 0 8px;"><strong>Livrabil:</strong> {{ $submission->deliverable?->title ?? '-' }}</p>
                        <p style="margin:0 0 8px;"><strong>Proiect:</strong> {{ $submission->project?->title ?? '-' }}</p>
                        <p style="margin:0 0 8px;"><strong>Nota:</strong> {{ number_format((float) ($submission->grade_points ?? 0), 2) }} / {{ number_format((float) ($submission->deliverable?->max_points ?? 0), 2) }}</p>
                        <p style="margin:0 0 8px;"><strong>Evaluat de:</strong> {{ $gradedBy->name }}</p>
                        <p style="margin:0;"><strong>Data evaluarii:</strong> {{ $submission->graded_at?->format('d.m.Y H:i') ?? now()->format('d.m.Y H:i') }}</p>
                    </td>
                </tr>
            </table>

            @if(!empty($submission->grade_feedback))
                <div style="margin:0 0 16px;padding:12px;border-radius:10px;background:#fff3e8;border:1px solid #f0d7c2;line-height:1.5;">
                    <strong>Feedback profesor:</strong><br>
                    {{ $submission->grade_feedback }}
                </div>
            @endif

            <p style="margin:0;">
                <a href="{{ route('deliverables.show', $submission->deliverable_id) }}" style="display:inline-block;background:#ea580c;color:#ffffff;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:700;">
                    Vezi livrabilul in platforma
                </a>
            </p>
        </td>
    </tr>
</table>
</body>
</html>

