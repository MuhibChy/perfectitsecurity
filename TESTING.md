# PerfectITSecurity — Testing

- Suite: PHPUnit, SQLite `:memory:`, ~390 tests, all green.
  `php artisan test` (≈6 min). No skipped/disabled tests.
- Key suites: `EndToEndWorkflowTest` (£1,500 staged workflow + closure +
  commission + reconciliation), `FinancialIntegrityTest` (staged/lifetime/
  overpay/duplicate/closure), `IndividualAccountsTest` (identity + money
  separation + isolation), `MemberIdentityTest` (ID lifecycle, QR revoke, 2FA,
  recovery, IDOR), `PresenceCertificateTest`, `TraceabilityTest`,
  `CustomerIsolationTest`, `SecurityAuthorizationTest`, `MfaTest`, `WalletTest`,
  `TrainingAcademyTest`, `StripeWebhookSecurityTest`.
- Synthetic data only (`[TEST]` prefixes, fake PNG bytes — no GD needed, no
  real identity/financial data, sandbox money rails).
- Production gates: `optimize:clear`, `route:list`, `migrate:status`,
  `php artisan test`, `npm run build`. Never edit tests to force PASS.
