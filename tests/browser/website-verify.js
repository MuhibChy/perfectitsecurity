// Standalone rendered verification: card heights, overflow, console errors,
// promo + dual-currency rendering across viewports. Uses system Chrome.
const { chromium } = require('playwright');
const { execSync, spawn } = require('child_process');
const path = require('path');
const fs = require('fs');

const ROOT = path.resolve(__dirname, '..', '..');
const BASE = 'http://127.0.0.1:8099';
const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';

async function main() {
  // Fresh disposable browser DB with catalogue + new website-population seeds.
  const dbPath = path.join(ROOT, 'database', 'database_browser.sqlite');
  fs.writeFileSync(dbPath, '');
  const env = { ...process.env, APP_ENV: 'playwright', TEST_SEED_PASSWORD: 'PW-Synthetic-Only-123!' };
  const run = (cmd) => execSync(cmd, { cwd: ROOT, stdio: 'pipe', env });
  run('php artisan --env=playwright cache:clear');
  run('php artisan --env=playwright migrate --force');
  run('php artisan --env=playwright db:seed --class=RoleTestUsersSeeder --force');
  run('php artisan --env=playwright db:seed --class=ServiceCatalogueSeeder --force');
  run('php artisan --env=playwright db:seed --class=AssetLifecycleCategorySeeder --force');
  run('php artisan --env=playwright db:seed --class=PromoCampaignSeeder --force');
  run('php artisan --env=playwright db:seed --class=DemoContentExpansionSeeder --force');

  const server = spawn('php', ['artisan', 'serve', '--env=playwright', '--host=127.0.0.1', '--port=8099'], { cwd: ROOT, env });
  await new Promise((r) => setTimeout(r, 6000));

  const browser = await chromium.launch({ executablePath: CHROME, args: ['--no-sandbox'] });
  const results = {};
  try {
    for (const width of [390, 768, 1280, 1920]) {
      const page = await browser.newPage({ viewport: { width, height: 900 } });
      const errors = [];
      page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text().slice(0, 160)); });
      page.on('pageerror', (e) => errors.push(String(e).slice(0, 160)));
      await page.goto(BASE + '/services', { waitUntil: 'networkidle' });
      const cards = await page.$$eval('article.term-panel', (els) => els.slice(0, 12).map((e) => Math.round(e.getBoundingClientRect().height)));
      const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
      const usdRefs = await page.$$eval('text=/USD \\$/', (els) => els.length).catch(() => -1);
      const promoBadges = await page.locator('.term-tag-accent:has-text("%")').count().catch(() => -1);
      await page.screenshot({ path: path.join(ROOT, 'tests', 'browser', 'artifacts', `services-${width}.png`) });
      await page.close();
      results[width] = { cards, overflow, usdRefs, promoBadges, consoleErrors: errors.slice(0, 5) };
    }
    // Industries + portfolio + case-study + pricing pages.
    for (const [name, url, needle] of [['industries', '/industries', 'Logistics, Transport'], ['portfolio', '/portfolio', 'Whitfield'], ['cases', '/case-studies', 'Multi-Office'], ['pricing', '/pricing', 'PROMOTION']]) {
      const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
      const errors = [];
      page.on('pageerror', (e) => errors.push(String(e).slice(0, 160)));
      await page.goto(BASE + url, { waitUntil: 'networkidle' });
      const found = (await page.content()).includes(needle);
      const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
      await page.screenshot({ path: path.join(ROOT, 'tests', 'browser', 'artifacts', `${name}-1280.png`) });
      await page.close();
      results[name] = { needleFound: found, overflow, consoleErrors: errors.slice(0, 5) };
    }
  } finally {
    await browser.close();
    server.kill();
  }
  console.log(JSON.stringify(results, null, 1));
}

main().catch((e) => { console.error('VERIFY-FAILED:', e.message); process.exit(1); });
