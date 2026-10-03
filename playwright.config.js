// Playwright configuration — isolated browser QA for PerfectITSecurity.
// Target: local staging-mirror instance on :8099 with --env=playwright
// (disposable database_browser.sqlite, log mailer, local-only AI, no live
// payment keys). Never point baseURL at a shared/staging/production host.
const { defineConfig } = require('@playwright/test');

const BASE_URL = process.env.PW_BASE_URL || 'http://127.0.0.1:8099';

module.exports = defineConfig({
  testDir: './tests/browser',
  testMatch: /.*\.spec\.js/,
  globalSetup: require.resolve('./tests/browser/global-setup.js'),
  timeout: 60 * 1000,
  expect: { timeout: 10 * 1000 },
  fullyParallel: false, // deterministic: shared disposable DB, ordered flows
  retries: 0, // never greenwash — failures stay visible
  workers: 1,
  reporter: [['list'], ['html', { outputFolder: 'tests/browser/artifacts/report', open: 'never' }]],
  use: {
    baseURL: BASE_URL,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'off',
    actionTimeout: 15 * 1000,
    navigationTimeout: 30 * 1000,
  },
  webServer: {
    command: 'php artisan --env=playwright serve --host=127.0.0.1 --port=8099',
    url: BASE_URL + '/login',
    // Never reuse: a stale server on :8099 (e.g. a manual staging serve)
    // would silently serve the WRONG database. Fail fast instead.
    reuseExistingServer: false,
    timeout: 120 * 1000,
    env: {
      TEST_SEED_PASSWORD: process.env.TEST_SEED_PASSWORD || '',
    },
  },
  outputDir: 'tests/browser/artifacts/results',
  projects: [
    { name: 'mobile-360', use: { viewport: { width: 360, height: 800 } } },
    { name: 'mobile-390', use: { viewport: { width: 390, height: 844 } } },
    { name: 'tablet-768', use: { viewport: { width: 768, height: 1024 } } },
    { name: 'landscape-1024', use: { viewport: { width: 1024, height: 768 } } },
    { name: 'desktop-1366', use: { viewport: { width: 1366, height: 768 } } },
    { name: 'wide-1920', use: { viewport: { width: 1920, height: 1080 } } },
  ],
});
