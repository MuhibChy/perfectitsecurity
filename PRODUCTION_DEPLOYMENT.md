# Production deployment gate

This application must not be deployed by copying the local `.env`. The local environment is intentionally configured for development (local/debug, SQLite, synchronous queue).

## Required before release

1. Provision a supported, patched PHP runtime (recommend PHP 8.2+), Composer, Node, MySQL 8+/MariaDB, TLS, and a persistent Redis-backed queue/cache.
2. Create a new production `.env` outside source control. Set at minimum:

   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-real-domain.example
   LOG_CHANNEL=stack
   LOG_LEVEL=warning
   DB_CONNECTION=mysql
   CACHE_DRIVER=redis
   QUEUE_CONNECTION=redis
   SESSION_DRIVER=database
   SESSION_SECURE_COOKIE=true
   SESSION_SAME_SITE=lax
   CORS_ALLOWED_ORIGINS=https://your-real-domain.example
   ```

3. Configure and prove delivery through a transactional mail provider. Supply `MAIL_MAILER`, host, port, username, password, encryption, and a real `MAIL_FROM_ADDRESS`; then complete a registration and verification-email test.
4. Select a payment provider and provide its account credentials, webhook signing secret, currency/country requirements, and refund policy. Payments must remain manual-only until a signed, idempotent webhook integration is implemented and tested against that provider's sandbox.
5. Run deployment commands as the deploy user:

   ```bash
   composer install --no-dev --prefer-dist --optimize-autoloader
   npm ci
   npm run build
   php artisan migrate --force
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan test
   ```

6. Run a persistent queue worker under Supervisor/systemd and a scheduler every minute:

   ```bash
   php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
   * * * * * cd /path/to/techsupport-platform && php artisan schedule:run >> /dev/null 2>&1
   ```

7. Set web-server document root to `public/`; deny access to `.env`, `storage/`, `vendor/`, and all non-public paths. Terminate TLS and forward trusted proxy headers correctly.
8. Move every existing file under `storage/app/public/ticket-attachments` to private `storage/app/ticket-attachments`, update each attachment record if paths differ, and verify customers cannot retrieve another customer's attachment.
9. Exercise this release checklist on staging with real test credentials: register/verify, create/reply/assign/resolve ticket, SLA warning/breach, service request → accepted quote → invoice, partial/full payment, commission payout, customer isolation, file download isolation, email delivery, and rollback.

## Release blockers still needing external input

- Payment provider credentials and signed webhook contract.
- Production mail provider credentials.
- Production hostname/TLS and allowed CORS origins.
- Redis/queue-worker and cron access.
- Legal-approved privacy policy, terms, cookie wording, and business-content claims.

Do not set the release status to ready until each item has a recorded staging verification result.

## Appendix A — server configuration samples

Required PHP extensions: `mbstring xml ctype json bcmath pdo_mysql tokenizer fileinfo curl zip gd intl redis`.
Database: MySQL 8+ / MariaDB 10.6+. Queue/cache: Redis 6+. Runtime: PHP 8.2+ recommended (repo currently runs Laravel 9 on PHP 8.0 — EOL; plan upgrade).

Nginx (TLS terminated, docroot `public/`):
```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /var/www/techsupport-platform/public;
    index index.php;
    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ { fastcgi_pass unix:/run/php/php8.2-fpm.sock; include fastcgi_params; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; }
    location ~ /\.(env|git) { deny all; }
    location ~ ^/(storage|vendor|database|tests)/ { deny all; }
}
```

Supervisor queue worker (`/etc/supervisor/conf.d/techsupport-worker.conf`):
```ini
[program:techsupport-worker]
command=php /var/www/techsupport-platform/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
```

Scheduler cron + log rotation + backup:
```bash
* * * * * cd /var/www/techsupport-platform && php artisan schedule:run >> /dev/null 2>&1
# Pre-migrate backup (run before every migrate --force):
mysqldump -h $DB_HOST -u $DB_USER -p$DB_PASSWORD $DB_DATABASE | gzip > /backups/techsupport-$(date +%F-%H%M).sql.gz
tar -czf /backups/techsupport-files-$(date +%F-%H%M).tar.gz -C /var/www/techsupport-platform storage/app public/build
# Rollback: php artisan migrate:rollback --force (after restoring the mysqldump on staging first)
```

Health checks:
- LB/K8s probe: `GET /healthz` (no auth; `{"status":"ok","db":"ok"}` or 503).
- Admin center: `/admin/health` (admin only) + `GET /admin/health/errors`.
- Post-deploy: `php artisan migrate:status`, `php artisan queue:failed`, `curl -f https://your-domain.com/healthz`.

