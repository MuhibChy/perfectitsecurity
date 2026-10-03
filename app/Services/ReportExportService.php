<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\Expense;
use App\Models\FinancialTransaction;
use App\Models\Franchise;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\ServiceOrder;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

/**
 * Report export builder (PDF + CSV, filter-bound, RBAC-enforced at the
 * controller layer). Every builder returns title/summary/columns/rows so
 * screen, CSV and PDF always agree. Currencies are never summed across
 * codes — per-currency totals only.
 */
class ReportExportService
{
    public const TYPES = ['customer', 'customer-full', 'employee-service', 'employee-finance', 'commission', 'franchise', 'financial', 'service', 'payment', 'task'];

    public function build(string $type, array $filters, User $viewer): array
    {
        abort_unless(in_array($type, self::TYPES, true), 404, 'Unknown report type.');

        return match ($type) {
            'customer' => $this->customer($filters, $viewer),
            'customer-full' => $this->customerFull($filters, $viewer),
            'employee-service' => $this->employeeService($filters, $viewer),
            'employee-finance' => $this->employeeFinance($filters, $viewer),
            'commission' => $this->commission($filters, $viewer),
            'franchise' => $this->franchise($filters, $viewer),
            'financial' => $this->financial($filters, $viewer),
            'service' => $this->service($filters, $viewer),
            'payment' => $this->payment($filters, $viewer),
            'task' => $this->task($filters, $viewer),
        };
    }

    /** Filters: date_from/date_to/customer_id/employee_id/agent_id/worker_id/franchise_id/service_id/order_id/project_id/status/payment_status/currency/branch. */
    protected function dates(array $f): array
    {
        $from = ! empty($f['date_from']) ? Carbon::parse($f['date_from'])->startOfDay() : null;
        $to = ! empty($f['date_to']) ? Carbon::parse($f['date_to'])->endOfDay() : null;

        return [$from, $to, ($f['date_from'] ?? null).' → '.($f['date_to'] ?? null)];
    }

    protected function inRange($q, string $col, $from, $to)
    {
        if ($from) {
            $q->where($col, '>=', $from);
        }
        if ($to) {
            $q->where($col, '<=', $to);
        }

        return $q;
    }

    protected function byCurrency($items, callable $amount): array
    {
        $out = [];
        foreach ($items as $i) {
            $code = strtoupper($i->currency ?? 'USD');
            $out[$code] = round(($out[$code] ?? 0) + (float) $amount($i), 2);
        }

        return $out;
    }

    // ── customer ────────────────────────────────────────────────
    protected function customer(array $f, User $viewer): array
    {
        $customer = User::findOrFail($f['customer_id'] ?? 0);
        abort_unless($customer->isCustomer(), 404);
        // Ownership: customers see only themselves; staff need boundary (route-gated).
        abort_unless($viewer->isStaff() || (int) $viewer->id === (int) $customer->id, 403);
        [$from, $to, $period] = $this->dates($f);

        $orders = $this->inRange($customer->serviceOrders()->with('service'), 'service_orders.created_at', $from, $to)->get();
        if (! empty($f['service_id'])) {
            $orders = $orders->where('service_id', (int) $f['service_id']);
        }
        if (! empty($f['status'])) {
            $orders = $orders->where('status', $f['status']);
        }
        $invoices = $this->inRange($customer->invoices(), 'invoices.created_at', $from, $to)->get();
        $payments = $this->inRange($customer->payments()->where('status', 'completed'), 'payments.created_at', $from, $to)->get();
        if (! empty($f['currency'])) {
            $payments = $payments->where('currency', strtoupper($f['currency']));
        }
        $refunds = $this->inRange($customer->payments()->where('status', 'refunded'), 'payments.created_at', $from, $to)->get();
        $tickets = $this->inRange($customer->tickets(), 'tickets.created_at', $from, $to)->get();
        $projects = $this->inRange($customer->projects(), 'projects.created_at', $from, $to)->get();

        $paidByCur = $this->byCurrency($payments, fn ($p) => $p->amount);
        $dueByCur = [];
        foreach ($invoices as $inv) {
            $code = strtoupper($inv->currency ?? 'USD');
            $dueByCur[$code] = round(($dueByCur[$code] ?? 0) + (float) $inv->amount_due, 2);
        }

        $rows = [];
        foreach ($orders as $o) {
            $rows[] = [$o->order_number, $o->service->name ?? '', $o->status, $o->currency, $o->total, $o->amount_paid, $o->amount_due, $o->created_at->format('Y-m-d')];
        }

        return [
            'title' => "Customer Report — {$customer->name}",
            'subject' => $customer,
            'period' => $period,
            'summary' => [
                'Customer' => "{$customer->name} <{$customer->email}>",
                'Company' => $customer->company_name ?? '—',
                'Country' => $customer->country ?? '—',
                'Orders' => $orders->count(),
                'Invoiced total' => round((float) $invoices->sum('total'), 2),
                'Paid (by currency)' => json_encode($paidByCur),
                'Outstanding (by currency)' => json_encode($dueByCur),
                'Refunds' => $refunds->count(),
                'Tickets' => $tickets->count(),
                'Projects' => $projects->count(),
            ],
            'columns' => ['Order', 'Service', 'Status', 'Currency', 'Total', 'Paid', 'Due', 'Date'],
            'rows' => $rows,
        ];
    }

    // ── employee service ────────────────────────────────────────
    protected function employeeService(array $f, User $viewer): array
    {
        $employee = User::findOrFail($f['employee_id'] ?? 0);
        abort_unless(! $employee->isCustomer(), 404);
        $selfStaff = $viewer->isStaff() && ((int) $viewer->id === (int) $employee->id || $viewer->isAdmin() || $viewer->isProjectManager() || $viewer->isFinanceManager() || $viewer->isSupportManager());
        $selfContractor = $viewer->isFreelancer() && (int) $viewer->id === (int) $employee->id;
        abort_unless($selfStaff || $selfContractor, 403);
        [$from, $to, $period] = $this->dates($f);

        $tasks = $this->inRange($employee->tasks()->with(['customer', 'serviceOrder.service']), 'tasks.created_at', $from, $to)->get();
        if (! empty($f['status'])) {
            $tasks = $tasks->where('status', $f['status']);
        }
        $contrib = $employee->contributedTasks()->with(['customer', 'serviceOrder.service'])->get();
        $tickets = $this->inRange($employee->assignedTickets(), 'tickets.created_at', $from, $to)->get();

        $rows = [];
        foreach ($tasks->concat($contrib)->unique('id') as $t) {
            $rows[] = [$t->task_number ?? $t->id, $t->title ?? '', $t->customer->name ?? '', $t->serviceOrder->order_number ?? '', $t->pivot->role ?? 'assignee', $t->status, $t->progress ?? '', $t->created_at->format('Y-m-d'), $t->completed_at?->format('Y-m-d') ?? ''];
        }
        $done = $tasks->where('status', 'completed')->count() + $contrib->where('status', 'completed')->count();

        return [
            'title' => "Employee Service Report — {$employee->name}",
            'subject' => $employee,
            'period' => $period,
            'summary' => [
                'Employee' => "{$employee->name} <{$employee->email}>",
                'Employee no.' => $employee->employee_number ?? '—',
                'Department/Branch' => ($employee->department ?? '—').' / '.($employee->branch ?? '—'),
                'Assigned tasks' => $tasks->count(),
                'Contributions' => $contrib->count(),
                'Completed' => $done,
                'Tickets handled' => $tickets->count(),
            ],
            'columns' => ['Task', 'Title', 'Customer', 'Order', 'Role', 'Status', 'Progress', 'Assigned', 'Completed'],
            'rows' => $rows,
        ];
    }

    // ── employee finance (finance/admin only) ───────────────────
    protected function employeeFinance(array $f, User $viewer): array
    {
        abort_unless($viewer->isFinanceManager(), 403, 'Employee financial reports require finance authorization.');
        $employee = User::findOrFail($f['employee_id'] ?? 0);
        [$from, $to, $period] = $this->dates($f);

        $salaries = $this->inRange($employee->salaries(), 'salaries.created_at', $from, $to)->get();
        $commissions = $this->inRange($employee->commissions(), 'commissions.created_at', $from, $to)->get();
        $transfers = $this->inRange($employee->bankTransfers(), 'bank_transfers.created_at', $from, $to)->get();
        $expenses = Expense::where('worker_id', $employee->id);
        $this->inRange($expenses, 'expenses.created_at', $from, $to);
        $expenses = $expenses->get();

        $rows = [];
        foreach ($salaries as $s) {
            $rows[] = ['salary', "#{$s->id} {$s->period}", $s->currency ?? 'USD', $s->net_salary, $s->status, $s->pay_date];
        }
        foreach ($commissions as $c) {
            $rows[] = ['commission', $c->commission_number, 'USD', $c->commission_amount, "{$c->status}/{$c->payment_status}", $c->created_at->format('Y-m-d')];
        }
        foreach ($transfers as $t) {
            $rows[] = ['transfer', "{$t->reference} ({$t->purpose})", $t->currency, $t->amount, $t->status, $t->created_at->format('Y-m-d')];
        }
        foreach ($expenses as $e) {
            $rows[] = ['expense', $e->expense_number, 'USD', $e->amount, $e->status, $e->date];
        }

        return [
            'title' => "Employee Financial Report — {$employee->name} (CONFIDENTIAL)",
            'subject' => $employee,
            'period' => $period,
            'summary' => [
                'Employee' => "{$employee->name} <{$employee->email}>",
                'Salary records' => $salaries->count(),
                'Commissions' => $commissions->count(),
                'Transfers' => $transfers->count(),
                'Expenses' => $expenses->count(),
            ],
            'columns' => ['Kind', 'Reference', 'Currency', 'Amount', 'Status', 'Date'],
            'rows' => $rows,
            'confidential' => true,
        ];
    }

    // ── commission ──────────────────────────────────────────────
    protected function commission(array $f, User $viewer): array
    {
        // Commission agents/freelancers may view ONLY their own ledger rows;
        // finance managers keep global access. Never recalculated here.
        if (! $viewer->isFinanceManager()) {
            abort_unless($viewer->isFreelancer() || $viewer->isStaff(), 403);
            $f['agent_id'] = (int) $viewer->id;
            $f['worker_id'] = (int) $viewer->id;
            unset($f['customer_id']);
        }
        [$from, $to, $period] = $this->dates($f);
        $q = Commission::with(['worker', 'customer', 'rule', 'task.serviceOrder.service', 'project.service', 'approver']);
        $this->inRange($q, 'commissions.created_at', $from, $to);
        if (! empty($f['agent_id']) || ! empty($f['worker_id'])) {
            $q->where('worker_id', (int) ($f['agent_id'] ?? $f['worker_id']));
        }
        if (! empty($f['status'])) {
            $q->where('status', $f['status']);
        }
        if (! empty($f['customer_id'])) {
            $q->where('customer_id', (int) $f['customer_id']);
        }
        $rows = [];
        foreach ($q->get() as $c) {
            $payoutRef = CommissionPayout::whereHas('items', fn ($x) => $x->where('commission_id', $c->id))->latest()->first();
            $orderNo = $c->task?->serviceOrder?->order_number ?? '';
            $serviceName = $c->task?->serviceOrder?->service?->name ?? $c->project?->service?->name ?? '';
            $rows[] = [$c->commission_number, $c->worker->name ?? '', $c->customer->name ?? '', $orderNo, $serviceName, $c->rule->name ?? '', $c->revenue_amount, $c->commission_rate, $c->commission_amount, $c->status, $c->approver->name ?? '', $c->approved_at?->format('Y-m-d') ?? '', $c->payment_status, $c->paid_at?->format('Y-m-d') ?? '', $payoutRef->transaction_reference ?? ''];
        }

        return [
            'title' => 'Commission Report',
            'period' => $period,
            'summary' => ['Records' => count($rows), 'Total commission' => round(array_sum(array_column($rows, 8)), 2)],
            'columns' => ['Commission', 'Agent', 'Customer', 'Order', 'Service', 'Rule', 'Base', 'Rate %', 'Amount', 'Status', 'Approved by', 'Approved at', 'Payment', 'Paid at', 'Provider ref'],
            'rows' => $rows,
        ];
    }

    // ── franchise ───────────────────────────────────────────────
    protected function franchise(array $f, User $viewer): array
    {
        abort_unless($viewer->isFinanceManager(), 403);
        $franchise = Franchise::with('owner', 'members')->findOrFail($f['franchise_id'] ?? 0);
        [$from, $to, $period] = $this->dates($f);
        $memberIds = $franchise->members()->pluck('id');
        $orders = ServiceOrder::whereIn('customer_id', $memberIds);
        $this->inRange($orders, 'service_orders.created_at', $from, $to);
        $orders = $orders->with('customer')->get();
        $payments = Payment::whereIn('customer_id', $memberIds)->where('status', 'completed');
        $this->inRange($payments, 'payments.created_at', $from, $to);
        $payments = $payments->get();
        $outstanding = ServiceOrder::whereIn('customer_id', $memberIds)->sum('amount_due');
        $rows = [];
        foreach ($orders as $o) {
            $rows[] = [$o->order_number, $o->customer->name ?? '', $o->status, $o->currency, $o->total, $o->amount_paid, $o->amount_due];
        }

        return [
            'title' => "Franchise Report — {$franchise->name}",
            'subject' => $franchise,
            'period' => $period,
            'summary' => [
                'Franchise' => "{$franchise->name} ({$franchise->franchise_code})",
                'Members' => $memberIds->count(),
                'Orders' => $orders->count(),
                'Revenue collected' => round((float) $payments->sum('amount'), 2),
                'Outstanding' => round((float) $outstanding, 2),
            ],
            'columns' => ['Order', 'Customer', 'Status', 'Currency', 'Total', 'Paid', 'Due'],
            'rows' => $rows,
        ];
    }

    // ── financial ───────────────────────────────────────────────
    protected function financial(array $f, User $viewer): array
    {
        abort_unless($viewer->isFinanceManager(), 403);
        [$from, $to, $period] = $this->dates($f);
        $q = FinancialTransaction::with('creator');
        $this->inRange($q, 'financial_transactions.created_at', $from, $to);
        if (! empty($f['status'])) {
            $q->where('status', $f['status']);
        }
        if (! empty($f['currency'])) {
            $q->where('currency', strtoupper($f['currency']));
        }
        $txns = $q->latest()->limit(2000)->get();
        $rows = [];
        foreach ($txns as $t) {
            $rows[] = [$t->created_at->format('Y-m-d H:i'), $t->transaction_id, $t->type, $t->category, mb_substr((string) $t->description, 0, 120), $t->currency, $t->amount, $t->status];
        }
        $byTypeCur = [];
        foreach ($txns->where('status', 'completed') as $t) {
            $k = $t->type.'|'.strtoupper($t->currency ?? 'USD');
            $byTypeCur[$k] = round(($byTypeCur[$k] ?? 0) + (float) $t->amount, 2);
        }

        return [
            'title' => 'Financial Report',
            'period' => $period,
            'summary' => array_merge(['Records' => count($rows)], $byTypeCur),
            'columns' => ['Date', 'Reference', 'Type', 'Category', 'Description', 'Currency', 'Amount', 'Status'],
            'rows' => $rows,
        ];
    }

    // ── customer-full (360° + financial summary + merged ledger) ──
    protected function customerFull(array $f, User $viewer): array
    {
        $base = $this->customer($f, $viewer);
        $customer = $base['subject'];
        [$from, $to] = $this->dates($f);

        $orders = $this->inRange($customer->serviceOrders()->with('service'), 'service_orders.created_at', $from, $to)->get();
        $invoices = $this->inRange($customer->invoices(), 'invoices.created_at', $from, $to)->get();
        $payments = $this->inRange($customer->payments(), 'payments.created_at', $from, $to)->get();
        $tasks = Task::where('customer_id', $customer->id);
        $this->inRange($tasks, 'tasks.created_at', $from, $to);
        $tasks = $tasks->with('serviceOrder')->get();

        $completed = $payments->where('status', 'completed');
        $refunded = $payments->where('status', 'refunded');
        $cancelledOrders = (clone $orders)->where('status', 'cancelled');

        $rows = [];
        foreach ($orders as $o) {
            $rows[] = [$o->created_at->format('Y-m-d H:i'), 'order', $o->order_number, $o->service->name ?? '', $o->status, $o->currency.' '.$o->total];
        }
        foreach ($invoices as $i) {
            $rows[] = [$i->created_at->format('Y-m-d H:i'), 'invoice', $i->invoice_number, '', $i->status, $i->currency.' '.$i->total];
        }
        foreach ($payments as $p) {
            $rows[] = [($p->paid_at ?? $p->created_at)->format('Y-m-d H:i'), 'payment', $p->payment_number.' / '.$p->transaction_id, $p->payment_method, $p->status, $p->currency.' '.$p->amount];
        }
        foreach ($tasks as $t) {
            $rows[] = [$t->created_at->format('Y-m-d H:i'), 'task', $t->task_number ?? $t->id, $t->serviceOrder->order_number ?? '', $t->status, ''];
        }
        usort($rows, fn ($a, $b) => strcmp((string) $b[0], (string) $a[0]));

        $base['title'] = "Customer Full Report — {$customer->name}";
        $base['summary'] = array_merge($base['summary'], [
            'Total quoted' => round((float) Quotation::where('customer_id', $customer->id)->sum('total'), 2),
            'Total ordered' => round((float) $orders->sum('total'), 2),
            'Discounts given' => round((float) $orders->sum('discount_amount'), 2),
            'Tax charged' => round((float) $orders->sum('tax_amount'), 2),
            'Total paid (completed)' => round((float) $completed->sum('amount'), 2),
            'Refunded amount' => round((float) $refunded->sum('amount'), 2),
            'Cancelled order value' => round((float) $cancelledOrders->sum('total'), 2),
            'Payment records' => $payments->count(),
            'Invoice records' => $invoices->count(),
            'Task records' => $tasks->count(),
        ]);
        $base['columns'] = ['Date', 'Kind', 'Reference', 'Detail', 'Status', 'Amount'];
        $base['rows'] = $rows;

        return $base;
    }

    // ── payment ─────────────────────────────────────────────────
    protected function payment(array $f, User $viewer): array
    {
        [$from, $to, $period] = $this->dates($f);
        $q = Payment::with(['customer', 'serviceOrder.service']);
        $this->inRange($q, 'payments.created_at', $from, $to);
        if (! empty($f['customer_id'])) {
            $cid = (int) $f['customer_id'];
            abort_unless($viewer->isStaff() || (int) $viewer->id === $cid, 403);
            $q->where('customer_id', $cid);
        } elseif (! $viewer->isStaff()) {
            $q->where('customer_id', $viewer->id);
        }
        if (! empty($f['order_id'])) {
            $q->where('service_order_id', (int) $f['order_id']);
        }
        if (! empty($f['status'])) {
            $q->where('status', $f['status']);
        }
        if (! empty($f['payment_method'])) {
            $q->where('payment_method', $f['payment_method']);
        }
        if (! empty($f['currency'])) {
            $q->where('currency', strtoupper($f['currency']));
        }
        $rows = [];
        foreach ($q->latest()->limit(2000)->get() as $p) {
            $rows[] = [$p->payment_number, ($p->paid_at ?? $p->created_at)->format('Y-m-d H:i'), $p->customer->name ?? '', $p->serviceOrder->order_number ?? '', $p->serviceOrder->service->name ?? '', $p->payment_method, $p->transaction_id, $p->currency.' '.$p->amount, $p->status];
        }

        return [
            'title' => 'Payment Report',
            'period' => $period,
            'summary' => ['Records' => count($rows)],
            'columns' => ['Payment', 'Date', 'Customer', 'Order', 'Service', 'Method', 'Transaction', 'Amount', 'Status'],
            'rows' => $rows,
        ];
    }

    // ── task ────────────────────────────────────────────────────
    protected function task(array $f, User $viewer): array
    {
        [$from, $to, $period] = $this->dates($f);
        $q = Task::with(['customer', 'assignee', 'serviceOrder.service']);
        $this->inRange($q, 'tasks.created_at', $from, $to);
        if (! empty($f['customer_id'])) {
            $cid = (int) $f['customer_id'];
            abort_unless($viewer->isStaff() || (int) $viewer->id === $cid, 403);
            $q->where('customer_id', $cid);
        } elseif (! $viewer->isStaff()) {
            $q->where('customer_id', $viewer->id);
        }
        if (! empty($f['employee_id'])) {
            $q->where('assigned_to', (int) $f['employee_id']);
        }
        if (! empty($f['order_id'])) {
            $q->where('service_order_id', (int) $f['order_id']);
        }
        if (! empty($f['status'])) {
            $q->where('status', $f['status']);
        }
        $rows = [];
        foreach ($q->latest()->limit(2000)->get() as $t) {
            $rows[] = [$t->task_number ?? $t->id, $t->title ?? '', $t->customer->name ?? '', $t->serviceOrder->order_number ?? '', $t->assignee->name ?? '—', $t->status, $t->created_at->format('Y-m-d'), $t->completed_at?->format('Y-m-d') ?? ''];
        }

        return [
            'title' => 'Task Report',
            'period' => $period,
            'summary' => ['Records' => count($rows)],
            'columns' => ['Task', 'Title', 'Customer', 'Order', 'Technician', 'Status', 'Created', 'Completed'],
            'rows' => $rows,
        ];
    }

    // ── service (order) ─────────────────────────────────────────
    protected function service(array $f, User $viewer): array
    {
        $order = ServiceOrder::with(['customer', 'service', 'tasks.assignee', 'tickets', 'invoices.payments', 'payments'])->findOrFail($f['order_id'] ?? 0);
        abort_unless($viewer->isStaff() || (int) $viewer->id === (int) $order->customer_id, 403);
        $paid = round((float) $order->payments()->where('status', 'completed')->sum('amount'), 2);
        $rows = [];
        foreach ($order->tasks as $t) {
            $rows[] = ['task', $t->task_number ?? $t->id, $t->assignee->name ?? '—', $t->status, $t->created_at->format('Y-m-d'), $t->completed_at?->format('Y-m-d') ?? ''];
        }
        foreach ($order->payments as $p) {
            $rows[] = ['payment', $p->payment_number, $p->payment_method, "{$p->status}", $p->currency.' '.$p->amount, $p->paid_at?->format('Y-m-d') ?? ''];
        }

        return [
            'title' => "Service Report — {$order->order_number}",
            'subject' => $order,
            'period' => $order->created_at->format('Y-m-d').' → '.($order->closed_at?->format('Y-m-d') ?? 'open'),
            'summary' => [
                'Customer' => $order->customer->name,
                'Service' => $order->service->name ?? '',
                'Service status' => $order->status,
                'Financial' => $order->payment_status,
                'Total/Paid/Due' => "{$order->total} / {$order->amount_paid} / {$order->amount_due} {$order->currency}",
                'Reconciled (TOTAL=PAID+DUE)' => \App\Services\PaymentState::balancesReconcile((float) $order->total, (float) $order->amount_paid, (float) $order->amount_due) ? 'YES' : 'NO',
            ],
            'columns' => ['Kind', 'Reference', 'Who/Method', 'Status', 'Detail', 'Date'],
            'rows' => $rows,
        ];
    }
}
