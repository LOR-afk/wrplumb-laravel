<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $heading }}</title>
</head>
<body style="margin:0;background:#f4f7fb;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f7fb;padding:28px 12px;">
<tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;">
<tr><td style="padding:22px 26px;background:#0b67b3;color:#ffffff;">
<div style="font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">WRPlumb</div>
<div style="font-size:22px;font-weight:700;margin-top:5px;">{{ $heading }}</div>
</td></tr>
<tr><td style="padding:26px;">
<p style="margin:0 0 14px;font-size:15px;">Hello {{ $recipientName }},</p>
<p style="margin:0 0 20px;font-size:15px;line-height:1.6;">{{ $bodyMessage }}</p>
@if (!empty($details))
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:0 0 22px;">
@foreach ($details as $label => $value)
<tr>
<td style="padding:9px 10px;border-bottom:1px solid #edf2f7;font-size:13px;font-weight:700;width:34%;">{{ $label }}</td>
<td style="padding:9px 10px;border-bottom:1px solid #edf2f7;font-size:13px;">{{ $value }}</td>
</tr>
@endforeach
</table>
@endif
@if (!empty($actionUrl) && !empty($actionLabel))
<a href="{{ $actionUrl }}" style="display:inline-block;padding:11px 18px;background:#0b67b3;color:#ffffff;text-decoration:none;border-radius:8px;font-size:14px;font-weight:700;">{{ $actionLabel }}</a>
@endif
<p style="margin:24px 0 0;color:#6b7280;font-size:12px;line-height:1.5;">This is an automated WRPlumb workflow notification.</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
