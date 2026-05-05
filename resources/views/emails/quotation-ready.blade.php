<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your WRPlumb Quotation is Ready</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f8fc; padding:24px; color:#1f2a37;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border:1px solid #e3ebf3; border-radius:18px; overflow:hidden;">
        <div style="padding:24px; background:#0f4c81; color:#ffffff;">
            <h2 style="margin:0;">WR Plumbing and Construction Services</h2>
            <p style="margin:6px 0 0;">Your service quotation is ready for review.</p>
        </div>

        <div style="padding:24px;">
            <p>Hello <strong>{{ $clientName }}</strong>,</p>

            <p>
                Your quotation for <strong>{{ $serviceType }}</strong> has been prepared.
                Please review the details and confirm whether you accept or decline the quotation.
            </p>

            <div style="background:#f8fbff; border:1px solid #e3ebf3; border-radius:14px; padding:16px; margin:18px 0;">
                <p style="margin:0 0 8px;"><strong>Quotation No.:</strong> {{ $quotation->quotation_no }}</p>
                <p style="margin:0 0 8px;"><strong>Total Amount:</strong> PHP {{ number_format((float) $quotation->grand_total, 2) }}</p>
                <p style="margin:0;"><strong>Status:</strong> {{ strtoupper($quotation->status) }}</p>
            </div>

            <p style="text-align:center; margin:26px 0;">
                <a href="{{ $publicUrl }}"
                   style="background:#1d9bf0; color:#ffffff; padding:13px 22px; border-radius:12px; text-decoration:none; font-weight:bold; display:inline-block;">
                    View Quotation
                </a>
            </p>

            <p style="font-size:13px; color:#6b7280;">
                If the button does not work, copy and open this link:
            </p>

            <p style="font-size:13px; word-break:break-all;">
                <a href="{{ $publicUrl }}">{{ $publicUrl }}</a>
            </p>

            <p>Thank you,<br><strong>WR Plumbing and Construction Services</strong></p>
        </div>
    </div>
</body>
</html>