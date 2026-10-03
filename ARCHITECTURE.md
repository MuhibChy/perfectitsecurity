# PerfectITSecurity — Architecture

Laravel 9 + PHP 8.0 + SQLite (dev/test; MySQL supported) + Blade/Tailwind/Alpine + Vite.
Single coherent platform: one `users` identity feeds CRM, ERP, ITSM, finance,
messaging, identity, Academy learning, KB and AI.

## Authoritative entities (one source of truth each)

| Entity | Table(s) | Owner service |
|---|---|---|
| Identity | `users` (+`member_number` CUS-/EMP-/…) | Auth controllers, `RoleRegistry` |
| Per-user settings | `user_settings` (typed KV) | `UserSetting` |
| Customer work | `service_orders` → invoices/tickets/tasks (generated once) | `ServiceOrderWorkflowService` |
| Money in | `payments` (completed) → `receipts`/`cash_memos` (1:1 docs) → `financial_transactions` (income) | `ServiceOrderWorkflowService`, `WalletService`, `StripePaymentService` |
| Money out | `salaries`→`bank_transfers`, `commissions`→`commission_payouts` | `BankTransferService`, `CommissionService` |
| Wallet | `wallets`/`wallet_transactions` (own ledger, linked) | `WalletService` |
| Conversation | `direct_messages` (+ticket/task/project threads) | `MessagingService` |
| Identity proof | `identity_documents` (private) → `member_id_cards` (signed QR) | `IdentityVerificationService`, `MemberIdCardService` |
| Learning | `training_courses→modules→lessons`, quizzes/attempts, assignments, `training_certificates` | `TrainingService` |

## Key invariants

- One business event = one transaction; profiles/dashboards/reports/receipts are VIEWS.
- Lifetime totals are always `SUM()` over authoritative rows (never stored sole-source).
- State changes go through service methods with row locks, unique keys and audit rows.
- `users.role` CHECK is generated from `RoleRegistry::definitions()` (migration
  `2026_09_19_000006`) — add roles in code AND migrate the check.

## Request flow

`web.php` (~500 routes) → `auth` → `customer|staff` → `requires.role:*` →
`mfa` → controller → service (transaction) → audit + notify.
Public QR/certificate verification is throttled and discloses minimal data.
