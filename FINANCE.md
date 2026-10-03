# PerfectITSecurity — Finance & Accounting Integrity

## Principle

One business event = one authoritative transaction. Invoices, receipts, cash
memos, wallet entries, dashboards and reports REFERENCE it — never re-book it.

## Lifecycles

- Payments: canonical `PaymentState` (SUCCEEDED vs derived PAID); overpay blocked;
  refunds are separate `RFD-` rows (originals preserved, balances moved back,
  closed orders refuse refunds).
- Salaries: pending → approved → paid ONLY via completed `bank_transfers`
  (external reference mandatory, requester≠approver, sandbox-marked).
- Commissions: pending → submitted → under_review → approved → payable → paid
  (paid lands only on payout completion against a real reference).
- Expenses: draft → … → approved ≠ paid (`paid_at` + `payment_reference` required).
- Orders close only with tasks complete + zero due; closure preserves all history.

## Rules enforced in code

- All money `decimal(…,2)`, rounded per mutation, 1p reconciliation tolerance.
- Unique numbers/keys: invoice, payment, transaction, receipt (1:1), cash memo
  (1:1), wallet event, bank idempotency, salary (`SAL-`), commission, payout, expense.
- Per-currency grouping — never summed across currencies; per-currency P&L.
- Lifetime paid = `SUM(payments WHERE completed)`; lifetime earnings =
  `SUM(salaries WHERE paid)` + `SUM(commissions WHERE paid)`. No stored sole-source totals.
- P&L counts approved-but-unpaid commission as accrued cost (documented choice).

## Verification

`FinancialIntegrityTest`, `EndToEndWorkflowTest`, `FinancialReconciliationTest`,
`TraceabilityService::consistencyCheck`, `admin/history/consistency` dashboard.
