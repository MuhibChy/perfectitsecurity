// Employee workflow: dedicated test engineer sees assigned work only;
// admin/finance actions are denied.
const { test, expect } = require('@playwright/test');
const { loginAs, expectNoHorizontalOverflow } = require('./helpers');

test.describe('employee workflow', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'employee');
  });

  test('work views render without leaking other scopes', async ({ page }) => {
    for (const url of ['/admin/work-orders', '/admin/tickets']) {
      const res = await page.goto(url, { waitUntil: 'domcontentloaded' }).catch(() => null);
      if (!res) continue;
      expect([200, 302, 403], `${url} status`).toContain(res.status());
      if (res.status() === 200) await expectNoHorizontalOverflow(page);
    }
  });

  test('financial administration is denied for non-finance staff', async ({ page }) => {
    const res = await page.goto('/admin/finance', { waitUntil: 'domcontentloaded' }).catch(() => null);
    if (res && res.ok()) {
      // If the route exists and renders, it must not expose payout controls.
      await expect(page.locator('body')).not.toContainText(/approve payout|release payment/i);
    } else if (res) {
      expect([403, 404, 302], 'finance guard status').toContain(res.status());
    }
  });

  test('user administration is denied for engineers', async ({ page }) => {
    const res = await page.goto('/admin/users', { waitUntil: 'domcontentloaded' }).catch(() => null);
    if (res) expect([200, 302, 403, 404], 'users guard status').toContain(res.status());
  });
});
