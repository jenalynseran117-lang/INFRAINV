<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;padding:0;background:#F7F7F5;font-family:Arial,Helvetica,sans-serif;color:#0B1229;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#F7F7F5;padding:32px 12px;">
  <tr><td align="center">
    <table role="presentation" width="560" cellspacing="0" cellpadding="0" style="max-width:560px;width:100%;background:#ffffff;border-radius:16px;border:1px solid #ECE7D8;">
      <tr><td style="padding:28px 32px 8px;">
        <div style="font-size:12px;font-weight:700;letter-spacing:2px;color:#C9A24B;">INFRA-INV SYSTEM</div>
        <h1 style="margin:8px 0 0;font-size:22px;color:#0B1229;">Your email was registered</h1>
      </td></tr>
      <tr><td style="padding:16px 32px 8px;font-size:15px;line-height:1.6;color:#374151;">
        <p style="margin:0 0 14px;">Hello {{ $name }},</p>
        <p style="margin:0 0 14px;">
          This email address (<strong>{{ $email }}</strong>) was just registered to your
          <strong>INFRA-INV</strong> account as <strong>{{ $roleLabel }}</strong>.
        </p>
        @if ($verifyUrl)
        <p style="margin:0 0 20px;">Please verify this email address by clicking the button below:</p>
        <p style="margin:0 0 24px;text-align:center;">
          <a href="{{ $verifyUrl }}" style="display:inline-block;background:#16233F;color:#ffffff;text-decoration:none;font-weight:bold;padding:13px 28px;border-radius:10px;">Verify my email address</a>
        </p>
        <p style="margin:0 0 14px;font-size:12px;color:#6B7280;word-break:break-all;">
          If the button doesn't work, copy this link into your browser:<br>{{ $verifyUrl }}
        </p>
        @endif
        <p style="margin:0;padding:12px 14px;background:#FFF8E6;border:1px solid #F1E2B5;border-radius:8px;font-size:13px;color:#7A5C10;">
          Didn't make this change? Contact the system administrator right away.
        </p>
      </td></tr>
      <tr><td style="padding:20px 32px 28px;font-size:12px;color:#9CA3AF;">
        Infrastructure &amp; Supply Office &middot; This is an automated message, please do not reply.
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>