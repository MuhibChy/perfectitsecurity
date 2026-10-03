<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans,sans-serif;color:#111} .box{border:2px solid #1e40af;border-radius:12px;padding:24px;max-width:520px} h1{text-align:center;letter-spacing:4px;font-size:20px} .sub{text-align:center;color:#555;font-size:11px} .row{margin:6px 0;font-size:13px} .qr{text-align:center;margin-top:14px}</style></head>
<body><div class="box">
<h1>PERFECTITSECURITY</h1><p class="sub">DIGITAL MEMBER ID</p>
<p class="row"><strong>{{ $user->name }}</strong></p>
<p class="row">Member ID: {{ $user->member_number }}</p>
<p class="row">Role: {{ $user->roleDisplayName() }}</p>
<p class="row">Status: {{ ($user->is_active ?? true) ? 'ACTIVE' : 'INACTIVE' }} · Identity: {{ strtoupper($user->identity_status) }} · 2FA: {{ $user->hasMfaEnabled() ? 'ENABLED' : 'NOT ENABLED' }}</p>
<p class="row">Card: {{ $card->card_number }} ({{ strtoupper($card->status) }}) · Issued: {{ optional($card->issued_at)->format('Y-m-d') }}</p>
<div class="qr">{!! $qrSvg !!}<p class="sub">Scan to verify</p></div>
</div></body></html>
