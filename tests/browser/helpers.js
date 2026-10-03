// Shared browser helpers — role locators, console-error capture, layout guards.
// No secrets, tokens, or customer records are written to traces/artifacts.
const { expect } = require('@playwright/test');

const PASSWORD = () => {
  const pw = process.env.TEST_SEED_PASSWORD;
  if (!pw) throw new Error('TEST_SEED_PASSWORD env var is required');
  return pw;
};

const USERS = {
  customer: 'customer.test@example.test',
  employee: 'engineer.test@example.test',
  admin: 'admin.test@example.test',
};

async function dismissCookies(page) {
  // Cookie banner overlays lower controls; choose minimal cookies first,
  // then confirm it detached before interacting underneath it.
  const banner = page.getByRole('button', { name: /essential only/i });
  if (await banner.count()) {
    await banner.first().click().catch(() => {});
    await expect(banner.first()).toBeHidden({ timeout: 5000 }).catch(() => {});
  }
}

async function loginAs(page, role) {
  await page.goto('/login');
  await dismissCookies(page);
  await page.getByLabel(/email/i).fill(USERS[role]);
  await page.getByLabel(/^password/i).fill(PASSWORD());
  // php artisan serve (php -S) is single-threaded and can take seconds to
  // answer the login POST while the page holds asset requests open. The
  // default click() navigation-wait races that and times out even though
  // the login itself succeeds (dashboard snapshot proves it). Decouple the
  // click from the navigation wait and assert the landing URL explicitly.
  await Promise.all([
    page.waitForURL((url) => !/\/login/.test(url.pathname), { timeout: 30000 }).catch(() => null),
    page.getByRole('button', { name: /sign in|log in|login/i }).click({ noWaitAfter: true }),
  ]);
  // Landing differs per role; any authenticated landing counts.
  await expect(page).not.toHaveURL(/login/, { timeout: 30000 });
}

function consoleErrors(page) {
  const errors = [];
  page.on('console', (msg) => {
    if (msg.type() === 'error') errors.push(msg.text().slice(0, 300));
  });
  page.on('pageerror', (err) => errors.push(String(err).slice(0, 300)));
  return errors;
}

function failedRequests(page) {
  const failed = [];
  page.on('requestfailed', (req) => failed.push(req.url().slice(0, 200)));
  page.on('response', (res) => {
    if (res.status() >= 400) failed.push(`${res.status()} ${res.url().slice(0, 200)}`);
  });
  return failed;
}

async function expectNoHorizontalOverflow(page) {
  // Compare against window.innerWidth (includes scrollbar gutter) so a
  // vertical scrollbar alone cannot trip the guard. Tolerance 2px for
  // sub-pixel rounding; genuine overflow exceeds it by far.
  const overflow = await page.evaluate(() => {
    const limit = window.innerWidth + 2;
    return {
      scrollWidth: document.documentElement.scrollWidth,
      innerWidth: window.innerWidth,
      offenders: Array.from(document.querySelectorAll('body *'))
        .filter((el) => {
          const r = el.getBoundingClientRect();
          if (r.width === 0 || r.height === 0) return false;
          // Off-canvas drawer (mobile sidebar with -translate-x-full) and
          // its children sit entirely off-screen by design: not overflow.
          if (r.right <= 0 || r.left >= window.innerWidth) return false;
          if (r.left >= -2 && r.right <= window.innerWidth + 2) return false;
          // Clipped by an overflow-hidden ancestor (e.g. decorative blobs
          // inside a clipped backdrop): not a real overflow.
          let node = el.parentElement;
          while (node && node !== document.body) {
            const ox = window.getComputedStyle(node).overflowX;
            if (ox === 'hidden' || ox === 'clip') return false;
            node = node.parentElement;
          }
          return true;
        })
        .slice(0, 5)
        .map((el) => {
          const cls = typeof el.className === 'string' ? el.className : '';
          return el.tagName + '.' + cls.slice(0, 60);
        }),
    };
  });
  expect(
    overflow.scrollWidth,
    `horizontal overflow via ${overflow.offenders.join(', ') || 'unknown'}`
  ).toBeLessThanOrEqual(overflow.innerWidth + 2);
  expect(overflow.offenders, 'elements sticking out of the viewport').toEqual([]);
}

module.exports = {
  PASSWORD,
  USERS,
  loginAs,
  dismissCookies,
  consoleErrors,
  failedRequests,
  expectNoHorizontalOverflow,
};
