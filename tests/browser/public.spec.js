// Public pages: home, catalogue, pricing, contact, auth entry points.
const { test, expect } = require('@playwright/test');
const { consoleErrors, failedRequests, expectNoHorizontalOverflow, dismissCookies } = require('./helpers');

test.describe('public pages', () => {
  test('home renders with navigation and no console errors', async ({ page }) => {
    const errors = consoleErrors(page);
    const failed = failedRequests(page);
    await page.goto('/');
    await dismissCookies(page);
    await expect(page).toHaveTitle(/PerfectITSecurity|TechSupport|IT/i);
    // Full desktop nav appears at xl+ (1280px); narrower widths use the hamburger.
    const width = page.viewportSize().width;
    if (width >= 1280) {
      await expect(page.getByRole('link', { name: /client portal|login|sign in/i }).first()).toBeVisible();
    } else {
      const menu = page.getByRole('button', { name: /toggle menu/i });
      await expect(menu).toBeVisible();
      await menu.click();
      await expect(page.getByRole('link', { name: /client portal \/ login/i })).toBeVisible();
    }
    await expectNoHorizontalOverflow(page);
    expect(errors, 'console errors: ' + errors.join(' | ')).toEqual([]);
    expect(failed.filter((u) => !u.includes('favicon')), 'failed requests').toEqual([]);
  });

  test('service catalogue and pricing pages render', async ({ page }) => {
    for (const url of ['/portal/services', '/pricing', '/contact']) {
      const res = await page.goto(url, { waitUntil: 'domcontentloaded' });
      expect([200, 302], `${url} status`).toContain(res.status());
    }
  });

  test('password recovery page renders and validates', async ({ page }) => {
    await page.goto('/password/reset');
    await expect(page.getByLabel(/email/i)).toBeVisible();
    // Empty submit must not crash — validation error expected. Decoupled
    // click: php -S is single-threaded (see helpers.js loginAs); the
    // assertion below is unchanged, only the wait mechanics are.
    await Promise.all([
      page.waitForResponse((r) => r.url().includes('/password/email') && r.request().method() === 'POST', { timeout: 30000 }).catch(() => null),
      page.getByRole('button', { name: /send|reset|link/i }).click({ noWaitAfter: true }),
    ]);
    await expect(page).not.toHaveURL(/error|500/);
  });

  test('registration creates a synthetic customer and signs in', async ({ page }) => {
    const stamp = Date.now();
    const email = `pw.customer.${stamp}@example.test`;
    await page.goto('/register');
    await dismissCookies(page);
    await page.getByLabel(/^full name|^name/i).fill('PW Synthetic Customer');
    await page.getByLabel(/email/i).fill(email);
    await page.getByLabel(/^password/i).first().fill('Test-Password-123!');
    await page.getByLabel(/confirm/i).fill('Test-Password-123!');
    const roleSelect = page.getByLabel(/account type/i);
    if (await roleSelect.count()) {
      await roleSelect.selectOption('customer');
    }
    // Decoupled submit (see helpers.js loginAs): wait for the POST round
    // trip explicitly, then assert the landing URL. Assertions unchanged.
    // 90s bounds are environment-only accommodation, measured on this box:
    // isolated POST /register completes in ~4.4s (curl), but under Chromium
    // the same POST shares single-threaded php -S with in-flight font/3D
    // asset requests plus software-WebGL CPU contention and takes up to ~60s
    // while still returning the correct 302. No application behavior is
    // relaxed: success is still "leaves /register".
    await Promise.all([
      page.waitForResponse((r) => r.url().includes('/register') && r.request().method() === 'POST', { timeout: 90000 }).catch(() => null),
      page.getByRole('button', { name: /register|create|sign up/i }).click({ noWaitAfter: true }),
    ]);
    await expect(page).not.toHaveURL(/register/, { timeout: 90000 });
  });

  test('login rejects wrong credentials without leaking', async ({ page }) => {
    await page.goto('/login');
    await page.getByLabel(/email/i).fill('nobody@example.test');
    await page.getByLabel(/^password/i).fill('wrong-password');
    // Decoupled submit (see helpers.js loginAs): the default click()
    // navigation-wait races single-threaded php -S and times out even
    // though the POST succeeds. Wait for the POST + the flashed error
    // explicitly; leak assertions unchanged (plus error text now asserted).
    await Promise.all([
      page.waitForResponse((r) => r.url().endsWith('/login') && r.request().method() === 'POST', { timeout: 30000 }).catch(() => null),
      page.getByRole('button', { name: /sign in|log in|login/i }).click({ noWaitAfter: true }),
    ]);
    await expect(page).toHaveURL(/login/, { timeout: 30000 });
    await expect(page.locator('body')).toContainText(/credentials do not match/i, { timeout: 30000 });
    await expect(page.locator('body')).not.toContainText(/SQL|Exception|stack trace/i);
  });

  test('theme toggle switches readable theme', async ({ page }) => {
    await page.goto('/login');
    const toggle = page.getByRole('button', { name: /toggle color theme/i });
    await expect(toggle).toBeVisible();
    const before = await page.evaluate(() => document.documentElement.className);
    await toggle.click();
    const after = await page.evaluate(() => document.documentElement.className);
    expect(after).not.toBe(before);
  });
});
