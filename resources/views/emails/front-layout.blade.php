<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Cardiware')</title>
</head>
<body style="margin:0;background:#f1f5f9;color:#1e293b;font-family:Tahoma,Arial,sans-serif;line-height:1.9;direction:ltr;text-align:left">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
    <tr><td align="center" style="padding:32px 12px">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#fff;border-radius:16px">
            <tr><td style="padding:24px 28px;background:#0f172a;color:#fff;font-size:20px;font-weight:bold;border-radius:16px 16px 0 0">Cardiware | Cardiware</td></tr>
            <tr><td style="padding:28px">@yield('content')</td></tr>
            <tr><td style="padding:16px 28px;border-top:1px solid #e2e8f0;color:#64748b;font-size:12px">This email was sent automatically; please do not reply.</td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
