# Preview Run Doc — TechSupport Platform

## Reproduce Uncommitted Artifacts
1. Copy `.env` from the main checkout if absent (it contains DB credentials and APP_KEY).
2. `cd techsupport-platform && composer install` (vendor/ is already present in the worktree).
3. `npm install` (node_modules/ is already present).
4. `npx vite build` — compiles `resources/css/app.css` and `resources/js/app.js` into `public/build/`.

## Run the Server
1. `php artisan serve --host=127.0.0.1 --port=8080` — starts the Laravel backend on port 8080.
2. Optionally: `npx vite --host=127.0.0.1 --port=5173` — starts the Vite HMR dev server for live CSS/JS changes. In production preview, the built assets in `public/build/` are served directly by `php artisan serve`.

## Notes
- Database: MySQL on localhost:3306, database `techsupport` (see .env).
- Port 8000 was occupied; 8080 is used instead.
- PHP 8.0.30 (XAMPP) is required.
