# PerfectITSecurity — RBAC

Single `users.role` column; catalogue in `App\Support\RoleRegistry`
(12 roles, capabilities, training slugs, registration policy).

| Role | Sees / does |
|---|---|
| customer | Own portal only: orders, invoices, payments, tickets, projects, wallet, documents, ID, learning |
| support_agent / support_manager | Tickets + SLA + assigned tasks; no finance, no user admin |
| project_manager | Projects/tasks/operations; no finance mutation |
| finance_manager | Invoices/payments/expenses/salaries/transfers/commissions/reports; no user admin |
| employee | Assigned tasks/work history/own earnings; no peer payroll |
| freelancer / commission_agent | Assigned work / own commissions only |
| sales_agent | CRM pipeline (leads/quotes/proposals/orders) |
| training_manager | Academy management + certificate revocation |
| admin / super_admin | Users, verification queue, settings, audit, backups |

Enforcement: `staff`/`customer` boundary middleware, `requires.role:*` gates,
policies (`Ticket, Project, Invoice, Quotation, Service, Wallet`), controller
ownership checks, IDOR-tested. Registration: only self-registration roles are
directly assigned; others become pending requests (`registrationOutcome`).
`users.role` DB CHECK mirrors the registry (migration `2026_09_19_000006`).
