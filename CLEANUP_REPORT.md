# CLEANUP REPORT — Keep-Verified-Features Pass

Date: 2026-09-12 | No git in workspace — rollback snapshot:
`C:\xampp\htdocs\IT Service freelace\pre-production-full-audit-2026-09-10\removed\`
(database.sqlite + .env-equivalents + all removed files with original paths).

## Removed files manifest (32 files + 1 alias line + 1 npm package + 12 env keys)

### Phase A — root dead file (1)
- `check_tables.php` (dev-only DB inspection script, zero references)

### Phase B — routes
- None (all 330 route lines map to live controller methods; no dead routes found)

### Phase C — dead code (2 files + 1 line)
- `app/Http/Controllers/Admin/OllamaController.php` (no routes/views/references)
- `app/Http/Middleware/EnsureUserHasRole.php` (superseded by RequiresRole)
- `app/Http/Kernel.php`: removed `'role' => ...EnsureUserHasRole::class` alias line

### Phase D — orphan Blade views (26 files, none rendered or linked)
- `resources/views/welcome.blade.php`
- `admin/audit-logs/{create,edit,show}.blade.php`
- `admin/blog/show.blade.php`, `admin/companies/show.blade.php`, `admin/kb/show.blade.php`
- `admin/commissions/{create,edit}.blade.php`
- `admin/expenses/edit.blade.php`
- `admin/financials/{create,edit,show}.blade.php`
- `admin/notifications/{create,edit,show}.blade.php`
- `admin/payments/{edit,show}.blade.php`
- `admin/reports/{create,edit,show}.blade.php`
- `admin/settings/{create,edit,show}.blade.php`
- `customer/service-request/{index,show}.blade.php`

### Phase E — partials/components
- Skipped: every component, layout, error page, email view, Alpine store, and JS/CSS file verified in use.

### Phase F — dependencies (1)
- `gsap` removed from `package.json` (zero imports; only a code comment mentioned it). `package-lock.json` regenerated via `npm uninstall`; `npm run build` clean.

### Phase G — dead config (12 env keys in `.env.example`)
- `PUSHER_APP_ID/KEY/SECRET/HOST/PORT/SCHEME/CLUSTER`, `VITE_PUSHER_*` (5). Kept `TRUSTED_PROXIES` (production checklist documents it).

## Per-phase verification (all green)

| Phase | Removed | Tests | Routes | Build |
|---|---|---|---|---|
| A | 1 file | 180/180 | 330 | n/a (no assets) |
| B | 0 (none dead) | 180/180 | 330 | n/a |
| C | 2 files + 1 line | 180/180 | 330 | n/a |
| D | 26 views | 180/180 | 330 | n/a (Blade compiles at runtime; view cache cleared) |
| E | 0 (all used) | — | — | — |
| F | 1 dep | 180/180 | 330 | ✓ built clean |
| G | 12 keys | 180/180 | 330 | n/a |

## Known non-cleanup issue (left untouched, for separate fix)
`EmailVerificationService` references `\App\Mail\EmailOtpMail`, which does not exist; the call is try/caught so real OTP email silently never sends (dev log fallback only). Requires creating the mailable — out of cleanup scope.
