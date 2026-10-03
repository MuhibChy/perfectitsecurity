# Browser QA (Playwright)

Isolated end-to-end tests against a disposable local instance. No real
mail, payments, cloud AI, or production data are reachable from these runs.

## Prerequisites

- PHP 8.0, Composer deps installed, Node 24, `npx playwright install chromium`.
- A free TCP port 8099 on 127.0.0.1.

## Run

PowerShell (from the project root):

```powershell
$env:TEST_SEED_PASSWORD = "PW-Synthetic-Only-123!"
npm run test:browser            # all six viewport projects
npm run test:browser:mobile     # 360x800 + 390x844
npm run test:browser:desktop    # 1366x768 + 1920x1080
```

`TEST_SEED_PASSWORD` is required and never committed: it seeds disposable
`@example.test` accounts (`customer.test`, `engineer.test`, `admin.test`)
into `database/database_browser.sqlite` only (gitignored).

## Isolation guarantees

- `global-setup.js` aborts unless the resolved DB path is exactly
  `database/database_browser.sqlite`, then rebuilds it (`migrate` + the
  synthetic `RoleTestUsersSeeder` / `ServiceCatalogueSeeder`).
- `.env.playwright`: `APP_DEBUG=false`, `MAIL_MAILER=log`,
  `QUEUE_CONNECTION=sync`, `AI_PROVIDER=ollama` with
  `AI_FALLBACK_ENABLED=false`, all payment keys empty, backups disabled.
- Specs use role-based locators, deterministic waits (no arbitrary sleeps),
  and capture traces/screenshots on failure under
  `tests/browser/artifacts/` (safe location, no secrets inside).

## Coverage

Public pages, registration/login/recovery, customer order + forged-price
neutralization, owner-scoped invoices, cross-account denial, employee
scope limits, admin catalogue/reports/health (secret-free), financial UI
states, six-viewport overflow sweep, 3D-obstruction guard, theme
contrast, keyboard/focus/labels/validation checks.

## Known manual follow-ups

Screen-reader pass, full reduced-motion audit, Firefox/WebKit runs
(Chromium headless shell only in this gate).
