# Run locally

## Quick start on Windows

From the `techsupport-platform` directory, double-click `run-local.bat` or run:

```bat
run-local.bat
```

It uses the dependency versions locked in `composer.lock` and `package-lock.json`, then opens the Laravel development server on <http://127.0.0.1:8000>. The window running `php artisan serve` remains the server process; press `Ctrl+C` in that window to stop it.

The script only creates `.env` if it is absent. Its generated local configuration uses SQLite at `database/database.sqlite`, file cache/session storage, a synchronous queue, and log-only mail. It never overwrites `.env`, resets a database, runs package upgrades, or kills processes it did not start.

## Prerequisites

| Tool | Required version / extension | Purpose |
| --- | --- | --- |
| PHP | 8.0.2+; `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`, `xml`, `tokenizer` | Laravel runtime |
| Composer | 2.x | PHP packages from `composer.lock` |
| Node.js + npm | Node 16+; Node 20 LTS recommended | Vite/Tailwind asset build |

XAMPP PHP is suitable once it is available on `PATH`. MySQL is not required for the default local setup.

## Manual setup

```bat
copy .env.example .env
composer install
npm ci
php artisan key:generate
php artisan migrate
npm run build
php artisan serve
```

For frontend hot reload, use a second terminal:

```bat
npm run dev
```

## Demo data and accounts

The checked-in SQLite database may already contain data. To create a throwaway, fully seeded demo database, run:

```bat
php artisan migrate:fresh --seed
```

This permanently replaces the database selected by `.env`. The primary demo accounts use password `password`, including `admin@techsupport.com` (super admin) and `alice@example.com` (customer). Do not use these credentials beyond local development.

## Local configuration notes

- Update `APP_URL` if a different port is used.
- Use `MAIL_MAILER=log` locally unless you configure a mail catcher/provider. Email output will be written to `storage/logs/laravel.log`.
- The AI feature needs `AI_API_KEY` and may additionally use `AI_MODEL` and `AI_BASE_URL`; no external AI call can succeed without a valid key.
- The default queue is synchronous. Run `php artisan queue:work` only after selecting a non-sync queue backend.
- The SLA checker is scheduled every five minutes. For local scheduler testing, run `php artisan schedule:work` in another terminal.

## Verification and common fixes

```bat
php artisan about
php artisan migrate:status
php artisan test
```

If assets are missing, rerun `npm run build`. If Laravel reports that the application key is missing, run `php artisan key:generate`. If PHP reports that SQLite is unavailable, enable `pdo_sqlite` in the PHP installation used by the command line.
