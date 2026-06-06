<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 20px; }
        .card { background: white; border-radius: 12px; padding: 30px; max-width: 500px; margin: 0 auto; }
        .header { background: #1d4ed8; color: white; padding: 20px; border-radius: 8px; text-align: center; margin-bottom: 24px; }
        .field { margin-bottom: 16px; }
        .label { font-size: 12px; color: #6b7280; text-transform: uppercase; font-weight: bold; }
        .value { font-size: 15px; color: #111827; margin-top: 4px; }
        .btn { display: inline-block; background: #1d4ed8; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: bold; margin-top: 20px; }
        .footer { text-align: center; color: #9ca3af; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <div style="font-size: 32px;">🔔</div>
        <h2 style="margin:8px 0 0">New Lead Captured!</h2>
        <p style="margin:4px 0 0; opacity:0.8; font-size:14px">Someone needs IT support</p>
    </div>

    <div class="field">
        <div class="label">👤 Name</div>
        <div class="value">{{ $name }}</div>
    </div>

    <div class="field">
        <div class="label">📧 Email</div>
        <div class="value">{{ $email }}</div>
    </div>

    <div class="field">
        <div class="label">🖥️ Issue Reported</div>
        <div class="value">{{ $issue }}</div>
    </div>

    <div class="field">
        <div class="label">🕐 Time</div>
        <div class="value">{{ now()->format('M d, Y h:i A') }}</div>
    </div>

    <a href="mailto:{{ $email }}?subject=Following up on your IT issue&body=Hi {{ $name }}, I wanted to follow up on your IT issue: {{ $issue }}"
       class="btn">
        📩 Follow Up Now
    </a>

    <div class="footer">
        {{ config('branding.company_name') }} — AI Helpdesk Agent
    </div>
</div>
</body>
</html>