@echo off
setlocal EnableExtensions
cd /d "%~dp0"

echo.
echo TechSupport Platform - local launcher
echo.

where php >nul 2>&1 || (echo ERROR: PHP 8.0.2+ is required on PATH.& goto :fail)
where composer >nul 2>&1 || (echo ERROR: Composer 2 is required on PATH.& goto :fail)
where node >nul 2>&1 || (echo ERROR: Node.js 16+ is required on PATH.& goto :fail)
where npm >nul 2>&1 || (echo ERROR: npm is required on PATH.& goto :fail)
php -m | findstr /i /c:"pdo_sqlite" >nul || (echo ERROR: Enable the PHP pdo_sqlite extension.& goto :fail)

if not exist vendor (
    echo Installing locked PHP dependencies...
    composer install --no-interaction --prefer-dist || goto :fail
)

if not exist .env (
    echo Creating .env from the local SQLite template...
    copy /y .env.example .env >nul || goto :fail
    php artisan key:generate --force || goto :fail
)

if not exist database\database.sqlite (
    echo Creating local SQLite database...
    type nul > database\database.sqlite
)

if not exist node_modules (
    echo Installing locked frontend dependencies...
    npm ci || goto :fail
)

echo Applying outstanding database migrations...
php artisan migrate --force || goto :fail

echo Building frontend assets...
npm run build || goto :fail

echo.
echo Ready at http://127.0.0.1:8000
echo Press Ctrl+C in this window to stop the local server.
php artisan serve --host=127.0.0.1 --port=8000
goto :eof

:fail
echo.
echo Local startup did not complete. See RUN_LOCALLY.md for requirements and fixes.
exit /b 1
