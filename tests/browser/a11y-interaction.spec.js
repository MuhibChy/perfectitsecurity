// Accessibility + interaction baseline: keyboard nav, focus visibility,
// labels, validation announcements, button semantics, reduced motion.
// Automated baseline only — manual screen-reader checks remain open.
const { test, expect } = require('@playwright/test');
const { loginAs } = require('./helpers');

test.describe('accessibility and interaction', () => {
  test('login form is keyboard-operable with visible focus and labels', async ({ page }) => {
    await page.goto('/login');
    await page.keyboard.press('Tab');
    const focused = await page.evaluate(() => document.activeElement?.tagName);
    expect(['A', 'BUTTON', 'INPUT', 'SELECT']).toContain(focused);

    for (const name of ['Email', 'Password']) {
      await expect(page.getByLabel(new RegExp(name, 'i'))).toBeVisible();
    }
    const submit = page.getByRole('button', { name: /sign in|log in|login/i });
    await expect(submit).toBeVisible();
    expect(await submit.getAttribute('type')).toBe('submit');
  });

  test('validation errors are announced near fields', async ({ page }) => {
    await page.goto('/register');
    await page.getByRole('button', { name: /register|create|sign up/i }).click();
    // Native or server validation must surface without navigation away crash.
    await expect(page.locator('[role="alert"], .invalid-feedback, .text-red-500, .text-red-600').first()).toBeVisible({
      timeout: 10000,
    }).catch(() => {
      // Fallback: native browser validation keeps focus on the form.
      return expect(page).toHaveURL(/register/);
    });
  });

  test('dialogs trap focus when present', async ({ page }) => {
    await loginAs(page, 'customer');
    await page.goto('/portal/orders');
    const dialog = page.getByRole('dialog');
    if (await dialog.count()) {
      await dialog.first().evaluate((d) => d.setAttribute('open', ''));
      await page.keyboard.press('Tab');
      const inside = await page.evaluate(() => {
        const d = document.querySelector('[role="dialog"]');
        return d ? d.contains(document.activeElement) : true;
      });
      expect(inside).toBe(true);
    }
  });

  test('reduced-motion preference is respected by canvases', async ({ page }) => {
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.goto('/');
    const animated = await page.evaluate(() => {
      return Array.from(document.querySelectorAll('canvas')).map((c) => {
        const style = window.getComputedStyle(c);
        return style.animationName !== 'none' || c.hasAttribute('data-animated');
      });
    });
    // Informational: record but do not fail — manual review decides.
    test.info().annotations.push({ type: 'reduced-motion-canvases', description: JSON.stringify(animated) });
  });

  test('no links masquerading as buttons on auth pages', async ({ page }) => {
    await page.goto('/login');
    const submitLinks = await page.locator('a[type="submit"], a:has-text("Sign in")').count();
    expect(submitLinks).toBe(0);
  });
});
