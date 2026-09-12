# PerfectITSecurity — TechSupport Platform

Professional international IT support and cybersecurity platform. It combines a
public marketing site, an authenticated customer portal, and a role-controlled
staff administration area covering support tickets, services, projects,
billing, reporting, and operational health.

> **Source-control note:** this repository contains source code only. Runtime
> secrets (`.env`), local databases (`*.sqlite`), user uploads, generated
> backups, logs, and build artifacts are intentionally excluded via
> `.gitignore`. Copy `.env.example` to `.env` and fill in your own values.

## Project Overview

- **Public site:** services catalogue, case studies, blog, knowledge base,
  careers, contact / service requests, quotations and proposals.
- **Customer portal:** dashboard, tickets with attachments, service orders,
  projects, invoices, receipts, payments (manual/offline plus Stripe online
  checkout), subscriptions, and documents.
- **Staff administration:** role-based access control (RBAC), ticket triage,
  SLA policies, commissions, expenses, salaries, financial reporting,
  AI-assisted support with escalation, backups, health checks, and audit logs.

## Features

- Ticketing system with categories, priorities, SLA tracking, and file attachments
- Service catalogue with country-specific pricing and online ordering
- Billing: invoices, receipts, quotations, manual payments, Stripe integration
- Customer / staff dashboards and reporting
- AI support assistant (OpenAI-compatible or local Ollama) with human escalation
- Encrypted application backups (database + files) with retention policies
- System health monitoring, audit logging, and security finding tracking
- Multi-language support (`lang/`), blog, knowledge base, and document library

## Technology Stack

| Layer    | Technology |
|----------|------------|
| Backend  | Laravel 9 (PHP 8.0.2+) |
| Auth     | Laravel Sanctum, session auth, RBAC |
| Database | SQLite (local/dev), MySQL (production) |
| Frontend | Blade + Tailwind CSS 3, Alpine.js, Vite 4, Three.js |
| Payments | Stripe PHP SDK (optional — offline payments work without it) |
| SMS/OTP  | Twilio SDK with local OTP fallback |
| PDFs     | barryvdh/laravel-dompdf |
| Testing  | PHPUnit 9 (`php artisan test`) |
| CI       | GitHub Actions (`.github/workflows/ci.yml`) |

## System Requirements

- PHP 8.0.2+ with `pdo_sqlite` (SQLite) and `pdo_mysql` (production)
- Composer 2
- Node.js 16+ (20 LTS recommended) and npm
- SQLite (bundled with PHP) for local development; MySQL 8 for production

## Installation

```bash
git clone <repository-url>
cd techsupport-platform

composer install

cp .env.example .env
php artisan key:generate

touch database/database.sqlite   # Git Bash / macOS / Linux
# Windows PowerShell: New-Item database\database.sqlite -ItemType File

php artisan migrate

npm install
npm run build

php artisan storage:link

php artisan serve   # http://127.0.0.1:8000
```

**Windows shortcut:** double-click `run-local.bat` — it checks prerequisites,
installs locked dependencies, creates a safe local SQLite environment when
needed, runs outstanding migrations, builds frontend assets, and starts the
server at <http://127.0.0.1:8000>. It never overwrites an existing `.env` and
never resets an existing database. See [RUN_LOCALLY.md](RUN_LOCALLY.md).

## Environment Configuration

All configuration lives in `.env` (never committed). Start from the safe
template:

```bash
cp .env.example .env
php artisan key:generate
```

Key variables (see `.env.example` for the full list):

```env
APP_NAME=TechSupport
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=

TWILIO_ACCOUNT_SID=
TWILIO_AUTH_TOKEN=
TWILIO_VERIFY_SERVICE_SID=

AI_PROVIDER=openai
AI_API_KEY=
AI_MODEL=gpt-4o-mini

BACKUP_ENCRYPTION_KEY=
```

Online payments (Stripe), Twilio Verify OTP, and AI features are all optional —
manual/offline payments, local OTP fallback, and the full portal work without
them. For production values and the production checklist, see the commented
section at the bottom of `.env.example` and
[PRODUCTION_DEPLOYMENT.md](PRODUCTION_DEPLOYMENT.md).

## Database Setup

Local development uses SQLite:

```bash
php artisan migrate            # apply outstanding migrations (safe)
```

For a disposable demo database **only** (destroys the selected database):

```bash
php artisan migrate:fresh --seed
```

Production uses MySQL — set `DB_CONNECTION=mysql` plus `DB_HOST`, `DB_PORT`,
`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` in `.env`, then run
`php artisan migrate --force`. The local `database/database.sqlite` file is
git-ignored and is never committed.

## Frontend Setup

```bash
npm install    # or: npm ci (clean install from package-lock.json)
npm run build  # production build into public/build/
npm run dev    # local dev server with hot reload
```

## Running the Application

```bash
php artisan serve
```

Useful commands:

```bash
composer install
npm ci
npm run build
php artisan migrate
php artisan test
php artisan route:list
php artisan optimize:clear
```

## Testing

```bash
php artisan test
```

CI (`.github/workflows/ci.yml`) runs code style (`pint --test`), migrations +
seeds on SQLite, the full test suite, and `composer audit` / `npm audit` on
every push to `main`/`develop` and every pull request to `main`.

## Production Deployment

See [PRODUCTION_DEPLOYMENT.md](PRODUCTION_DEPLOYMENT.md) for the full release
procedure, and [CODEBASE_OVERVIEW.md](CODEBASE_OVERVIEW.md) for architecture,
modules, persistence model, and the release checklist. In short:

1. Set `APP_ENV=production`, `APP_DEBUG=false`, and a real `APP_URL`.
2. Use MySQL + Redis (`CACHE_DRIVER`, `QUEUE_CONNECTION`, `SESSION_DRIVER`).
3. Run `composer install --no-dev --optimize-autoloader`, `npm ci && npm run build`.
4. Run `php artisan migrate --force` (backups run automatically per schedule).
5. Never reuse local/dev keys — rotate `APP_KEY`, `AI_API_KEY`, and payment
   secrets via your server's secret store.

## Security Notes

- `.env` and any `*.sqlite` database are git-ignored and must never be committed.
- `storage/app/backups/`, `storage/app/documents/`, and
  `storage/app/ticket-attachments/` contain operational/customer data and are excluded.
- Seeders contain only demo credentials (e.g. `DemoPass123!`); change or remove
  demo accounts before going live.
- If a secret is ever committed to Git history by accident, deleting the file is
  not enough — rotate/revoke that credential immediately.

## Environment Variables

See `.env.example` — it documents every supported variable with safe
placeholders and a production checklist. Do not invent variables; add new ones
to `.env.example` (empty) whenever `config/` gains a new `env()` key.

## Project Structure

```text
app/            Application code (models, controllers, services, middleware)
bootstrap/      Framework bootstrap and cached config
config/         Configuration (backup, services, cors, database, ...)
database/       Migrations, seeders, factories (local *.sqlite ignored)
docs/           Additional documentation
lang/           Localisation files
public/         Web root (build/ and storage/ artefacts ignored)
resources/      Blade views, CSS, JS
routes/         Route definitions (web, api, console, channels)
storage/        Logs, cache, uploads, backups (operational data ignored)
tests/          PHPUnit test suite
.github/        CI/CD workflows
```

Reference docs in this repo: [CODEBASE_OVERVIEW.md](CODEBASE_OVERVIEW.md),
[RUN_LOCALLY.md](RUN_LOCALLY.md), [PRODUCTION_DEPLOYMENT.md](PRODUCTION_DEPLOYMENT.md).

## License

Proprietary — all rights reserved. If you intend to make this repository
public, confirm licensing and confirm no proprietary, customer, or financial
implementation details are exposed. A **private** repository is recommended.
