// Global setup: verify the disposable browser DB target, then rebuild it
// with synthetic fixtures. Aborts unless the resolved DB path positively
// identifies the disposable database_browser.sqlite file.
const { execSync } = require('child_process');
const path = require('path');
const fs = require('fs');

module.exports = async () => {
  const root = path.resolve(__dirname, '..', '..');
  const dbPath = path.resolve(root, 'database', 'database_browser.sqlite');

  if (!dbPath.endsWith('database_browser.sqlite')) {
    throw new Error('Refusing setup: unexpected DB path ' + dbPath);
  }
  if (!process.env.TEST_SEED_PASSWORD) {
    throw new Error(
      'Refusing setup: set TEST_SEED_PASSWORD env var (synthetic browser accounts only). ' +
        'Example (PowerShell): $env:TEST_SEED_PASSWORD="PW-Synthetic-Only-123!"'
    );
  }

  // Fresh disposable database for every browser run. The file cache is
  // shared on disk and survives migrate:fresh, so stale throttle counters
  // would lock out freshly seeded accounts — clear it first.
  fs.writeFileSync(dbPath, '');
  const run = (cmd) =>
    execSync(cmd, {
      cwd: root,
      stdio: 'pipe',
      env: { ...process.env, TEST_SEED_PASSWORD: process.env.TEST_SEED_PASSWORD },
    });
  run('php artisan --env=playwright cache:clear');
  run('php artisan --env=playwright migrate --force');
  run('php artisan --env=playwright db:seed --class=RoleTestUsersSeeder --force');
  run('php artisan --env=playwright db:seed --class=ServiceCatalogueSeeder --force');
};
