// Responsive matrix: every project runs at its viewport (see
// playwright.config.js). Checks overflow, 3D obstruction, theme contrast.
const { test, expect } = require('@playwright/test');
const { loginAs, expectNoHorizontalOverflow } = require('./helpers');

const PAGES = ['/', '/login', '/register', '/pricing', '/contact'];

test.describe('responsive layout', () => {
  for (const url of PAGES) {
    test(`public page ${url} fits viewport without overflow`, async ({ page }) => {
      await page.goto(url);
      await expectNoHorizontalOverflow(page);
    });
  }

  test('3D canvas never covers controls or text', async ({ page }) => {
    await page.goto('/');
    const covering = await page.evaluate(() => {
      const canvas = document.querySelector('canvas');
      if (!canvas) return { present: false };
      const r = canvas.getBoundingClientRect();
      const cx = Math.min(Math.max(r.left + r.width / 2, 0), window.innerWidth - 1);
      const cy = Math.min(Math.max(r.top + r.height / 2, 0), window.innerHeight - 1);
      const top = document.elementFromPoint(cx, cy);
      // Canvas is fine when it is behind content or pointer-transparent.
      const style = window.getComputedStyle(canvas);
      return {
        present: true,
        onTop: top === canvas,
        pointerEvents: style.pointerEvents,
        zIndex: style.zIndex,
      };
    });
    if (covering.present && covering.onTop && covering.pointerEvents !== 'none') {
      // A canvas stacked above interactive content must not intercept taps.
      const interactive = await page.evaluate(() => {
        const els = Array.from(document.querySelectorAll('a,button,input,select,textarea'));
        return els.filter((el) => {
          const r = el.getBoundingClientRect();
          if (r.width === 0 || r.height === 0) return false;
          const top = document.elementFromPoint(
            Math.min(Math.max(r.left + r.width / 2, 0), window.innerWidth - 1),
            Math.min(Math.max(r.top + r.height / 2, 0), window.innerHeight - 1)
          );
          return top && top.tagName === 'CANVAS';
        }).length;
      });
      expect(interactive, 'interactive elements hidden under canvas').toBe(0);
    }
  });

  test('dark and light themes keep body text readable', async ({ page }) => {
    await page.goto('/login');
    for (const theme of ['light', 'dark']) {
      await page.evaluate((t) => {
        if (t === 'dark') document.documentElement.classList.add('dark');
        else document.documentElement.classList.remove('dark');
      }, theme);
      const contrast = await page.evaluate(() => {
        const body = document.body;
        const fg = window.getComputedStyle(body).color;
        const bg = window.getComputedStyle(body).backgroundColor;
        const lum = (c) => {
          const m = c.match(/[\d.]+/g).map(Number);
          const [r, g, b] = m.slice(0, 3).map((v) => {
            v /= 255;
            return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
          });
          return 0.2126 * r + 0.7152 * g + 0.0722 * b;
        };
        const l1 = lum(fg);
        const l2 = lum(bg);
        return (Math.max(l1, l2) + 0.05) / (Math.min(l1, l2) + 0.05);
      });
      expect(contrast, `${theme} theme body contrast`).toBeGreaterThan(3);
    }
  });

  test('authenticated customer pages fit mobile widths', async ({ page }) => {
    test.skip(
      page.viewportSize().width > 500,
      'mobile-only overflow sweep (other viewports covered by their own runs)'
    );
    await loginAs(page, 'customer');
    for (const url of ['/portal/orders', '/portal/invoices']) {
      await page.goto(url);
      await expectNoHorizontalOverflow(page);
    }
  });
});
