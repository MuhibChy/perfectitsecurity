# PerfectITSecurity — Security

## Authentication & 2FA

- Session auth + throttled login with attempt lockout and audit (`Auth\LoginController`).
- TOTP for all members (`Auth\MfaController`, `TotpService`); secrets `encrypted`
  at rest; 8 single-use hashed recovery codes; challenge enforced by
  `EnsureMfaVerified` for staff AND customers; disable/regen require TOTP + audit + notify.
- Email + phone OTP verification; `MustVerifyEmail` on User.

## Authorization

- Role-column RBAC (`RoleRegistry`, 12 roles) via `RequiresRole`/`Customer`/`Staff`
  middleware + 6 policies. Server-side everywhere; IDOR covered by tests.
- Identity verification approvals: admins only. ID documents: private disk,
  owner-or-admin download, every access audited. Avatars: authorized route with
  per-owner visibility. Certificates: owner-or-trainer view, public signed-URL verify.

## Transport & headers

- `SecurityHeaders` middleware: nosniff, DENY framing, strict referrer,
  restricted permissions-policy, same-origin COOP, CSP; HSTS only behind live HTTPS.
- Stripe webhooks signature-verified, fail-closed in production, idempotent.

## Secrets & data

- No secrets in source; `.env` + encrypted 2FA/recovery columns; QR generated
  locally (no third-party secret leakage). Audit logs never carry document
  bodies, secrets or codes. Backups encrypted (`backup:*` commands).

## Prod checklist

`APP_ENV=production`, `APP_DEBUG=false`, HTTPS proxy, `SESSION_SECURE_COOKIE=true`,
Stripe webhook secret set, mail driver real, scheduler (`schedule:run`) enabled,
`backup:run` on cron + `backup:monitor` alerts.
