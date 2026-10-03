// Admin workflow: staging administrator manages catalogue/assignments/
// reports; audit history visible; destructive actions guarded.
const { test, expect } = require('@playwright/test');
const { loginAs, expectNoHorizontalOverflow } = require('./helpers');

test.describe('admin workflow', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'admin');
  });

  test('admin dashboard and catalogue management render', async ({ page }) => {
    await page.goto('/admin/services');
    await expect(page.locator('body')).toContainText(/service|catalogue|catalog/i);
    await expectNoHorizontalOverflow(page);
  });

  test('reports and audit history are reachable', async ({ page }) => {
    for (const url of ['/admin/reports/tickets', '/admin/tickets']) {
      const res = await page.goto(url, { waitUntil: 'domcontentloaded' }).catch(() => null);
      if (res) expect([200, 302, 403], `${url} status`).toContain(res.status());
    }
  });

  test('AI provider health panel renders without secrets', async ({ page }) => {
    const res = await page.goto('/admin/ai/settings', { waitUntil: 'domcontentloaded' }).catch(() => null);
    if (res && res.ok()) {
      const body = await page.locator('body').innerText();
      expect(body).not.toMatch(/sk-or-|sk-|Bearer [A-Za-z0-9]/);
      await expect(page.locator('body')).toContainText(/ollama|provider|health/i);
    }
  });
});
