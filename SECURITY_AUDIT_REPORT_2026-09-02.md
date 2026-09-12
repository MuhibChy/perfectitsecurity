# IT Support Platform — Code Cleanup & Security Audit Report

**Date:** 2026-09-02  
**Scope:** Local Laravel application source, routes, migrations, tests, Composer/NPM dependency manifests, build output, and configured local database.  
**Safety posture:** No application file, database row/table, migration, route, dependency, asset, or feature was deleted. No dependency was upgraded or removed.

## Executive summary

The application booted, built, and passed all available automated tests. A confirmed authentication hardening gap was remediated: login requests now receive a server-side limit of five attempts per minute. The change has a regression test and does not alter successful sign-in, registration, reset, logout, customer, staff, or API flows.

The audit also confirmed significant upstream dependency advisories in the Laravel 9 / Symfony dependency tree. Those require a separately planned and compatibility-tested framework upgrade; they were not changed during this safety-first audit.

## Baseline and inventory

| Item | Result |
|---|---|
| Framework | Laravel 9.52.22 |
| Reviewed PHP files (`app`, `routes`, `config`, `database`, `tests`) | 344 |
| Migrations | 23, all applied locally |
| Automated tests available | 36 |
| Git safety point | Not available: the supplied workspace is not a Git repository |
| Removed files/dependencies/data | 0 |

## Security findings

| ID | OWASP area | Severity | Status | Summary |
|---|---|---:|---|---|
| SEC-001 | A07 Authentication failures | Medium | Fixed and tested | Login lacked server-side throttling, enabling repeated password guessing. |
| SEC-002 | A03 Software supply chain | High | Open — staged upgrade required | Composer reports 15 advisories affecting Laravel 9/Symfony packages. |
| SEC-003 | A02 Security misconfiguration | Medium | Requires production verification | Local environment uses development settings; production debug, HTTPS, cookie, secret rotation, and web-server configuration cannot be verified from this workspace. |
| SEC-004 | A01 Access control | High | Partially covered | Existing authorization tests cover AI conversation isolation, finance access, and assigned-ticket access. Fine-grained policies are still required for all operational resources before production use. |

### SEC-001 — Login rate limiting

**Root cause:** the POST login route had no throttling middleware.  
**Remediation:** added Laravel's `throttle:5,1` middleware to the login route.  
**Files changed:** `routes/web.php`, `tests/Feature/AuthenticationTest.php`.  
**Verification:** the new test makes five rejected attempts and verifies the sixth receives HTTP 429. The full test suite passes.

### SEC-002 — Dependency advisories

`composer audit --locked` reported 15 advisories across Laravel Framework and Symfony components, including high-severity framework, HTTP foundation, mail/mime, process, and routing advisories. `npm audit --omit=dev --audit-level=high` reported zero vulnerabilities.

**Required remediation:** stage an upgrade from Laravel 9 and its locked Symfony dependencies in a separate branch/backup with full compatibility, browser, mail, queue, and production smoke testing. Do not use an automatic major-version update against the live application.

### SEC-003 — Deployment controls

Repository configuration uses an explicit `CORS_ALLOWED_ORIGINS` setting with a local `APP_URL` fallback and does not use a wildcard origin in code. The local environment is not evidence of production security. Before release, verify `APP_DEBUG=false`, unique secrets outside source control, forced HTTPS/TLS, secure cookies, production CORS origins, backup restoration, queue/scheduler supervision, and web-server restrictions on `.env` and storage.

## Cleanup review

No candidate was deleted. Static review found no PHP debug calls (`dd`, `dump`, `var_dump`, `print_r`) and no shell-execution calls in application source. Remaining TODO comments concern unimplemented invoice PDF/email features and are retained because they represent live, referenced functionality rather than dead code.

## Regression and quality verification

| Check | Result |
|---|---|
| `php artisan test` | PASS — 36 tests |
| Authentication test suite | PASS — 8 tests |
| Customer/AI/finance authorization regression tests | PASS |
| Route/view audit tests | PASS |
| PHP syntax validation | PASS |
| `npm run build` | PASS |
| Composer manifest validation | PASS |
| NPM production dependency audit | PASS — 0 advisories |
| Composer dependency audit | FAIL — 15 upstream advisories, recorded above |

## Change log

| Change | Reason | Functional verification |
|---|---|---|
| Added login throttling | Reduces brute-force authentication attempts | Authentication regression suite and full suite pass |
| Added rate-limit regression test | Prevents the control from being removed accidentally | Test asserts HTTP 429 after five failed attempts |
| Generated production frontend build | Build verification only | Vite completed successfully |

## Final assessment

### 🟡 PRODUCTION READY WITH REMAINING ISSUES

The verified application functionality remains intact, and no cleanup deletion was performed. Production deployment should not proceed until the dependency upgrade is planned and tested, fine-grained authorization coverage is expanded, and the production-only security controls listed above are verified.
