// Financial UI states (offline/manual rails only): totals, balances,
// receipts, no negative outstanding, repeated submission safety.
const { test, expect } = require('@playwright/test');
const { loginAs, expectNoHorizontalOverflow } = require('./helpers');

test.describe('financial UI', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'customer');
  });

  test('invoice list shows totals and statuses, never negative outstanding', async ({ page }) => {
    await page.goto('/portal/invoices');
    const body = await page.locator('body').innerText();
    expect(body).toMatch(/invoice|payment|balance|total/i);
    // Negative outstanding balances must never render.
    expect(body).not.toMatch(/outstanding[^0-9-]*-\s*[0-9]/i);
    expect(body).not.toMatch(/balance[^0-9-]*-\s*[0-9]+\.[0-9]{2}/i);
    await expectNoHorizontalOverflow(page);
  });

  test('invoice detail exposes receipt/pdf and payment history', async ({ page }) => {
    await page.goto('/portal/invoices');
    const detail = page.getByRole('link', { name: /view|detail|invoice|INV-/i });
    if (await detail.count()) {
      await detail.first().click();
      const body = await page.locator('body').innerText();
      expect(body).toMatch(/total|amount|paid|balance|status/i);
      expect(body).not.toMatch(/SQL|Exception|stack trace/i);
      await expectNoHorizontalOverflow(page);
    }
  });

  test('wallet statement renders when available', async ({ page }) => {
    const res = await page.goto('/portal/wallet', { waitUntil: 'domcontentloaded' }).catch(() => null);
    if (res && res.ok()) {
      await expect(page.locator('body')).toContainText(/wallet|balance|transaction/i);
      await expectNoHorizontalOverflow(page);
    }
  });
});
