<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New message from {{ $contactMessage->name }}</title>
</head>
<body style="margin:0;padding:24px;background:#f4f6fb;font-family:system-ui,-apple-system,'Segoe UI',sans-serif;color:#1e293b;">
    <div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:12px;padding:28px;border:1px solid #e2e8f0;">
        <h1 style="margin:0 0 4px;font-size:20px;">New portfolio message</h1>
        <p style="margin:0 0 24px;color:#64748b;font-size:14px;">
            Sent {{ $contactMessage->created_at?->toDayDateTimeString() }}
        </p>

        <table style="width:100%;border-collapse:collapse;font-size:15px;">
            <tr>
                <th style="text-align:left;padding:8px 0;width:150px;color:#64748b;font-weight:600;">Name</th>
                <td style="padding:8px 0;">{{ $contactMessage->name }}</td>
            </tr>
            <tr>
                <th style="text-align:left;padding:8px 0;color:#64748b;font-weight:600;">Email</th>
                <td style="padding:8px 0;">{{ $contactMessage->email }}</td>
            </tr>
            <tr>
                <th style="text-align:left;padding:8px 0;color:#64748b;font-weight:600;">Phone</th>
                <td style="padding:8px 0;">{{ $contactMessage->phone ?: '—' }}</td>
            </tr>
            <tr>
                <th style="text-align:left;padding:8px 0;color:#64748b;font-weight:600;">Subject</th>
                <td style="padding:8px 0;">{{ $contactMessage->subject }}</td>
            </tr>
            <tr>
                <th style="text-align:left;padding:8px 0;color:#64748b;font-weight:600;">Budget</th>
                <td style="padding:8px 0;">{{ $contactMessage->budget?->label() ?? '—' }}</td>
            </tr>
        </table>

        <hr style="border:none;border-top:1px solid #e2e8f0;margin:24px 0;">

        <p style="margin:0;white-space:pre-line;font-size:15px;line-height:1.6;">{{ $contactMessage->message }}</p>
    </div>
</body>
</html>
