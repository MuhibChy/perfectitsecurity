# Codebase overview and CI/CD reference

## Purpose and architecture

TechSupport Platform is a server-rendered Laravel 9.52 application for operating an IT-services company. It is a single PHP application rather than a separate SPA/API deployment. Laravel routes invoke controllers, controllers use Eloquent models and application services, and Blade templates render the public site, customer portal, and staff console. Vite compiles the Tailwind CSS and small JavaScript/Alpine.js layer into `public/build`.

```
Browser
  -> Laravel routes (`routes/web.php`, `routes/api.php`)
  -> Controllers + middleware
  -> Services / Eloquent models
  -> SQLite locally; MySQL/MariaDB intended for production
  -> Blade views + Vite-built CSS/JS
```

## Runtime and build dependencies

| Area | Current implementation | CI/CD implication |
| --- | --- | --- |
| Language/framework | PHP ^8.0.2, Laravel 9.52.22 | Use PHP 8.2 or 8.3 for new CI images; retain PHP 8 compatibility until `composer.json` changes. |
| PHP packages | Composer, locked in `composer.lock` | Install with `composer install`, never `composer update` in CI/release. |
| Frontend | Vite 4, Tailwind CSS 3, Alpine.js | Use `npm ci` and `npm run build`; publish `public/build` with the application artifact. |
| Local database | SQLite, `database/database.sqlite` | CI can use SQLite and needs `pdo_sqlite`. The database file is runtime state, not a deploy artifact. |
| Production database | MySQL/MariaDB expected by deployment guidance | Provision it independently, migrate with `--force`, back up before releases. |
| Async/schedule | Local queue is `sync`; SLA command is due every five minutes | Production needs a persistent queue worker if Redis is adopted and cron/scheduler every minute. |
| Mail/AI | Mail is configuration-driven; AI uses an OpenAI-compatible service setting | Inject credentials as deployment secrets; never store them in source or artifacts. |
| Ollama (local LLM) | `OllamaMcpService` connects to a local Ollama server (`config/ollama.php`); used for KB-generation and AI fallback | Set `OLLAMA_BASE_URL`, `OLLAMA_MODEL`, `OLLAMA_TIMEOUT` in the deployment environment. Not required for basic operation. |

The local launcher requires PHP extensions `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`, `xml`, and `tokenizer`. Node 20 LTS and Composer 2 are the recommended developer/CI versions.

## Directory map

| Path | Responsibility |
| --- | --- |
| `app/Http/Controllers` | HTTP actions divided into `Public`, `Customer`, `Admin`, `Auth`, and `Api`. |
| `app/Http/Middleware` | Authentication, customer/staff boundaries, role checks, CSRF, and security headers. |
| `app/Models` | Eloquent model layer for all business records. |
| `app/Services` | Domain calculations/workflows: SLA, commissions, financials, audit, health, AI, email OTP, phone OTP, and Ollama MCP. |
| `app/Console` | `sla:check-deadlines`, scheduled every five minutes. |
| `routes/web.php` | Main browser routes and several web-middleware JSON endpoints. |
| `routes/api.php` | Sanctum-protected `/api/user` endpoint. |
| `database/migrations` | Complete schema history; do not edit migrations already released. |
| `database/seeders` | Demo/sample content and service catalogue seed data. |
| `resources/views` | Blade templates for public, portal, admin, auth, and manuals. |
| `resources/css`, `resources/js` | Tailwind entry stylesheet and Vite/Alpine JavaScript entry point. |
| `tests/Feature` | Authentication, authorization, tickets, SLA, finance, commissions, routes, and Blade regression tests. |
| `config` | Environment-based Laravel configuration. |
| `public` | Only web-server document root; includes Vite output after build. |

## Product modules and data domains

Public users can view the home/about/pricing/contact pages, service catalogue, blog, knowledge base, and useful links. Authentication supports registration, email verification, password resets, and Laravel Sanctum.

Customers use `/portal` to create and reply to tickets, download authorized attachments and invoices, review projects, browse services, submit service requests, and accept/reject quotations. The `auth`, `verified`, and `customer` middleware protect this area.

New customers must verify their email address (OTP via `EmailVerificationService`/`EmailOtpMail` → `portal/verify-email`) and phone number (OTP via `PhoneVerificationService` → `portal/verify-phone`) before gaining full portal access. OTP codes are SHA-256 hashed before storage, have a 10-minute lifetime, enforce a 60-second resend cooldown, and invalidate after five failed attempts.

Staff use `/admin`. Staff-level access provides company and service management, useful links, link submissions, notifications, and quotations. Narrower role middleware gates support tickets, project/tasks, financial records, content, users, settings/audit records, health data, and AI administration. Current role checks are implemented by `StaffMiddleware`, `CustomerMiddleware`, and `RequiresRole`/`EnsureUserHasRole`.

The principal persistence domains are:

- Identity and organization: users, companies, support teams, settings, notifications, audit logs.
- Support: tickets, messages, categories/subcategories, time entries, attachments, and SLA policies.
- Delivery: services, categories, country prices, service requests, projects, milestones/files/comments, and tasks/applications/comments/attachments.
- Revenue: quotations/items, invoices/items, payments, expenses/categories, financial transactions, commissions/rules/payouts, and salaries.
- Content: blog posts/categories/tags/comments, knowledge-base articles/categories/tags, useful links, and link submissions.
- Operations: system health checks, page health, error logs, and AI conversations/messages/usage/knowledge gaps/escalations/settings.

## Important execution paths

- Ticket SLA processing: `app/Console/Commands/CheckSlaDeadlines.php` calls `SlaService`; the scheduler registers it in `app/Console/Kernel.php` every five minutes with overlap protection.
- Financial, commission, audit, AI, and health behavior lives in services rather than only controllers. Change these services and their feature tests together when changing business rules.
- Frontend assets are declared in `vite.config.js` (`resources/css/app.css` and `resources/js/app.js`). Blade layouts consume the Vite manifest; a production release fails functionally if `npm run build` is omitted.
- The AI widget has unauthenticated web endpoints in `routes/web.php`. Its service configuration is in `config/services.php`; API usage must be controlled through configuration and application-level behavior before production.

## Local development contract

`.env.example` now describes a safe local SQLite setup. `run-local.bat` copies it only when no `.env` exists, creates `database/database.sqlite`, installs dependencies only when absent, runs additive migrations, builds assets, and serves at port 8000. It deliberately does not seed or reset data.

Use `php artisan migrate:fresh --seed` only for a disposable local database. It is destructive. Demo passwords and mail/AI configuration must never be promoted to production.

## CI pipeline baseline

No hosted CI workflow is currently committed. The following is the minimum repeatable pipeline, run on every pull request and main-branch commit:

1. Check out source and configure PHP 8.2/8.3 with SQLite extensions and Node 20.
2. Run `composer validate --strict` and `composer install --no-interaction --prefer-dist --no-progress`.
3. Run `npm ci` and `npm run build`.
4. Create a CI-only `.env` with `APP_ENV=testing`, `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`, `CACHE_DRIVER=array`, `SESSION_DRIVER=array`, `QUEUE_CONNECTION=sync`, and a generated `APP_KEY`.
5. Run `php artisan test`. Add `vendor/bin/pint --test` as a blocking formatting gate after the baseline cleanup below.
6. Save test reports/logs on failure and publish a build artifact containing the application source and `public/build` (or rebuild assets in the release stage from the same locked revisions).

Do not run `migrate:fresh --seed` against a shared/staging/production database. CI tests use the isolated testing database defined by `phpunit.xml`.

## CD pipeline baseline

Promote the immutable artifact from a green CI run to staging, then production. Deploy secrets through the platform secret store and create the production `.env` outside source control. The deploy identity should run:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

After migration, run a smoke test against the health/home route and representative login/authorization flows. Keep the previous release and database backup available for rollback; rollback application code independently from database migration reversal unless the migration is explicitly reversible and rehearsed.

The web server must serve `public/` only. Run the scheduler every minute and, when a non-sync production queue is selected, supervise `php artisan queue:work`. See `PRODUCTION_DEPLOYMENT.md` for the existing environment, mail, payment, TLS, attachment, and staging-verification gates.

## Release risks to resolve before production

- Laravel 9 and PHP 8.0 are supported by the code constraints but are aging; plan a framework/PHP support upgrade before a long-lived production launch.
- `vendor/bin/pint --test` currently fails: three standalone files have PHP parse errors (`check_tables.php`, `test4.php`, `test5.php`) and 128 pre-existing style violations are reported. Fix or remove those utilities and apply the approved project style before making Pint a required CI gate.
- Provide real production mail, payment, AI, hostname/TLS, Redis/worker, and legal-content decisions. These are external prerequisites, not values CI can infer.
- The repository has no Git metadata in this workspace. Place the project under version control before enabling CI/CD so the lockfiles, migrations, deployment scripts, and review history are traceable.
- Use a managed MySQL/MariaDB backup policy and a migration rehearsal on staging. Do not copy the local SQLite database to production.
