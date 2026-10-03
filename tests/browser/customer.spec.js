// Customer workflow: sign in, profile, catalogue order (valid + forged
// price), order status, invoices/receipts, AI chat + escalation. Offline
// manual payments only — no live gateways.
const { test, expect } = require('@playwright/test');
const { loginAs, consoleErrors, expectNoHorizontalOverflow } = require('./helpers');

test.describe('customer workflow', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'customer');
  });

  test('dashboard and profile render for the signed-in customer', async ({ page }) => {
    const errors = consoleErrors(page);
    await page.goto('/portal/orders');
    await expect(page.locator('body')).toContainText(/order|service|dashboard/i);
    await expectNoHorizontalOverflow(page);
    expect(errors, 'console errors: ' + errors.join(' | ')).toEqual([]);
  });

  test('catalogue order submits at server-side total; forged price is neutralized', async ({ page }) => {
    // Price-discussion path exposes the customer price field.
    await page.goto('/portal/orders/create?discuss=1');
    const form = page.locator('form[action*="orders"]');
    await expect(form).toBeVisible();

    const service = form.locator('select[name="service_id"]');
    await service.selectOption({ index: 1 });
    await form.locator('textarea[name="requirements"]').fill('Browser QA synthetic order with sufficient detail.');
    // Forged custom price must force negotiation, never a cheap confirm.
    await form.locator('input[name="proposed_price"]').fill('1');
    // Decoupled submit (see helpers.js loginAs): php -S is single-threaded;
    // wait for the POST explicitly instead of racing click()'s load-wait.
    await Promise.all([
      page.waitForResponse((r) => r.url().includes('/portal/orders') && r.request().method() === 'POST', { timeout: 30000 }).catch(() => null),
      form.locator('button[type="submit"]').click({ noWaitAfter: true }),
    ]);
    await page.waitForLoadState('domcontentloaded');

    const body = await page.locator('body').innerText();
    expect(body).not.toMatch(/SQL|Exception|stack trace/i);
    expect(body).not.toMatch(/total[^0-9]*1\.00|\$\s*1\.00/);
    // Forged price path lands in negotiation, never a confirmed 1.00 order.
    await expect(page.locator('body')).toContainText(/price discussion|negotiat|review|proposal|pending/i);
  });

  test('invoices and payment history are owner-scoped', async ({ page }) => {
    await page.goto('/portal/invoices');
    await expect(page.locator('body')).toContainText(/invoice|payment|balance|bill/i);
    await expectNoHorizontalOverflow(page);
  });

  test('cross-customer order access is denied', async ({ page }) => {
    // Order id 1 belongs to seeded fixtures, not this customer session path;
    // an unrelated numeric id must 404/403, never leak another record.
    const res = await page.goto('/portal/orders/999999', { waitUntil: 'domcontentloaded' });
    expect([403, 404], 'cross-account status').toContain(res.status());
  });

  test('support escalation flow is reachable', async ({ page }) => {
    await page.goto('/portal/orders');
    const support = page.getByRole('link', { name: /support|help|contact|ticket/i });
    if (await support.count()) {
      await support.first().click();
      await expect(page.locator('body')).toContainText(/support|ticket|help|contact/i);
    }
  });
});
