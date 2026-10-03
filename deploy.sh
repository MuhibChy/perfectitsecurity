#!/usr/bin/env bash
# deploy.sh — Production startup entrypoint for Railway/Render/cloud PHP hosting
# This script is executed by Nixpacks (or Docker ENTRYPOINT) on each deployment.
# It runs safe, non-destructive pre-flight checks before starting the application server.

set -e

echo "=== PerfectITSecurity — Cloud Deployment Bootstrap ==="

# --- 1. Ensure required environment variables are present ---
: "${APP_KEY:?APP_KEY is required. Generate one with: php artisan key:generate --show}"
: "${APP_URL:?APP_URL is required. Set to the canonical public HTTPS URL.}"
: "${PORT:=8080}"

# --- 2. Ensure SQLite database file exists (Railway ephemeral storage) ---
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    DB_FILE="${DB_DATABASE:-/app/database/database.sqlite}"
    if [ ! -f "$DB_FILE" ]; then
        echo "Creating SQLite database file at $DB_FILE"
        mkdir -p "$(dirname "$DB_FILE")"
        touch "$DB_FILE"
    fi
fi

# --- 3. Ensure storage directories exist ---
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
mkdir -p storage/app/public
mkdir -p bootstrap/cache

chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# --- 4. Run pending database migrations (non-destructive, --force required for prod) ---
echo "Running migrations..."
php artisan migrate --force

# --- 5. Seed database if it appears empty (only on fresh deploy, idempotent check) ---
USER_COUNT=$(php artisan tinker --execute="echo App\Models\User::count();" 2>/dev/null | tail -1 || echo "0")
if [ "$USER_COUNT" = "0" ]; then
    echo "Database appears empty — running demo seeder..."
    php artisan db:seed --force 2>&1 || echo "Seeding skipped (failed gracefully)"
fi

# --- 6. Clear and rebuild config, route, and view caches ---
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache 2>/dev/null || true

# --- 7. Verify APP_DEBUG is off ---
if [ "${APP_DEBUG:-false}" = "true" ]; then
    echo "WARNING: APP_DEBUG=true is set. This leaks internal details. Set APP_DEBUG=false for production."
fi

echo "Bootstrap complete. Starting server on port $PORT..."

# --- 8. Start the HTTP server ---
exec php artisan serve --host=0.0.0.0 --port="$PORT"
