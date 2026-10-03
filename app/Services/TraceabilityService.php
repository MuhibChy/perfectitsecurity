<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CustomerDocument;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\Proposal;
use App\Models\Quotation;
use App\Models\ServiceOrder;
use App\Models\ServiceRequest;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Collection;

/**
 * TraceabilityService — central business-history aggregator.
 *
 * No duplicate truth: every figure aggregates the authoritative records
 * (orders, invoices, payments, projects, tasks, tickets). Timelines are
 * generated from actual rows only — nothing is invented.
 */
class TraceabilityService
{
    // ── Customer 360° ──────────────────────────────────────────
    public static function customerOverview(User $customer): array
    {
        $id = $customer->id;
        $orders = ServiceOrder::where('customer_id', $id);
        $invoices = Invoice::where('customer_id', $id)->where('status', '!=', 'cancelled');
        $payments = Payment::where('customer_id', $id)->where('status', 'completed');
        $projects = Project::where('customer_id', $id);
        $tickets = Ticket::where('customer_id', $id);

        return [
            'customer_since' => $customer->created_at,
            'services' => (clone $orders)->distinct('service_id')->count('service_id'),
            'orders' => (clone $orders)->count(),
            'orders_total' => round((float) (clone $orders)->sum('total'), 2),
            'projects' => (clone $projects)->count(),
            'projects_completed' => (clone $projects)->where('status', 'completed')->count(),
            'tickets' => (clone $tickets)->count(),
            'tickets_open' => (clone $tickets)->whereNotIn('status', ['resolved', 'closed', 'cancelled'])->count(),
            'invoices' => (clone $invoices)->count(),
            'invoiced_total' => round((float) (clone $invoices)->sum('total'), 2),
            'paid_total' => round((float) $payments->sum('amount'), 2),
            'outstanding' => round((float) (clone $invoices)->sum('amount_due'), 2),
            'documents' => CustomerDocument::where('user_id', $id)->count(),
        ];
    }

    /**
     * Unified chronological timeline from actual records.
     * $context 'admin' links to staff routes, 'portal' to customer routes.
     *
     * @return Collection<int, array{at: mixed, label: string, detail: string, url: ?string}>
     */
    public static function customerTimeline(User $customer, string $context = 'admin', int $limit = 100): Collection
    {
        $id = $customer->id;
        $events = collect();
        $link = fn (?string $route, $param) => $route ? route($route, $param) : null;

        $events->push(['at' => $customer->created_at, 'label' => 'Customer account created', 'detail' => $customer->name, 'url' => null]);
        $add = function ($rows, string $label, callable $detail, ?string $route) use ($events, $link) {
            foreach ($rows as $row) {
                $events->push(['at' => $row->created_at, 'label' => $label, 'detail' => $detail($row), 'url' => $route ? $link($route, $row->id ?? $row->getKey()) : null]);
            }
        };

        if ($context === 'admin') {
            $add(ServiceRequest::where('user_id', $id)->orWhere('email', $customer->email)->latest()->take(20)->get(), 'Service request submitted', fn ($r) => $r->subject ?? ('Request ' . $r->request_number), null);
            $add(Lead::where('customer_id', $id)->latest()->take(10)->get(), 'Lead linked', fn ($r) => $r->name . ' (' . $r->status . ')', 'admin.leads.show');
            $add(Quotation::where('customer_id', $id)->latest()->take(20)->get(), 'Quotation ' . 'issued', fn ($r) => 'Total ' . $r->total . ' · ' . $r->status, 'admin.quotations.show');
            $add(ServiceOrder::where('customer_id', $id)->latest()->take(20)->get(), 'Order created', fn ($r) => $r->order_number . ' · ' . $r->status, 'admin.work-orders.show');
            $add(Payment::where('customer_id', $id)->latest()->take(20)->get(), 'Payment received', fn ($r) => $r->payment_number . ' · ' . $r->amount, null);
            $add(Invoice::where('customer_id', $id)->latest()->take(20)->get(), 'Invoice issued', fn ($r) => $r->invoice_number . ' · ' . $r->status, 'admin.invoices.show');
            $add(Project::where('customer_id', $id)->latest()->take(20)->get(), 'Project created', fn ($r) => $r->name . ' · ' . $r->status, 'admin.projects.show');
            $add(Ticket::where('customer_id', $id)->latest()->take(20)->get(), 'Ticket opened', fn ($r) => $r->ticket_number . ' · ' . $r->status, 'admin.tickets.show');
        } else {
            $add(ServiceOrder::where('customer_id', $id)->latest()->take(20)->get(), 'Order created', fn ($r) => $r->order_number . ' · ' . $r->status, 'portal.orders.show');
            $add(Payment::where('customer_id', $id)->latest()->take(20)->get(), 'Payment received', fn ($r) => $r->payment_number . ' · ' . $r->amount, null);
            $add(Invoice::where('customer_id', $id)->latest()->take(20)->get(), 'Invoice issued', fn ($r) => $r->invoice_number . ' · ' . $r->status, 'portal.invoices.show');
            $add(Project::where('customer_id', $id)->latest()->take(20)->get(), 'Project created', fn ($r) => $r->name . ' · ' . $r->status, 'portal.projects.show');
            $add(Ticket::where('customer_id', $id)->latest()->take(20)->get(), 'Ticket opened', fn ($r) => $r->ticket_number . ' · ' . $r->status, 'portal.tickets.show');
            $add(Quotation::where('customer_id', $id)->latest()->take(20)->get(), 'Quotation received', fn ($r) => 'Total ' . $r->total . ' · ' . $r->status, 'portal.quotations.show');
        }

        return $events->sortByDesc('at')->take($limit)->values();
    }

    /**
     * Unified per-profile transaction ledger.
     * Merged chronological entries shaped for profile display:
     * {at, type, description, status, reference, url}.
     * Read-only merge of owned records + own audit trail (logins, password
     * changes, edits). Source rows stay immutable; this is a view model.
     */
    public static function profileLedger(User $customer, string $context = 'portal', int $limit = 100): Collection
    {
        $id = $customer->id;
        $rows = collect();
        $push = function ($at, string $type, string $description, ?string $status, ?string $reference, ?string $url) use ($rows) {
            $rows->push(compact('at', 'type', 'description', 'status', 'reference', 'url'));
        };
        $link = fn (?string $route, $param) => $route ? route($route, $param) : null;

        $push($customer->created_at, 'account', 'Account created — ' . $customer->name, 'active', 'USR-' . $customer->id, null);
        if ($customer->email_verified_at) $push($customer->email_verified_at, 'verification', 'Email address verified', 'verified', null, null);
        if ($customer->phone_verified_at) $push($customer->phone_verified_at, 'verification', 'Mobile number verified', 'verified', null, null);
        if ($customer->identity_verified_at) $push($customer->identity_verified_at, 'verification', 'Government ID verified (' . ($customer->identity_status ?? '') . ')', (string) $customer->identity_status, null, null);

        $portal = $context !== 'admin';
        $rq = $portal ? 'portal.orders.show' : 'admin.work-orders.show';
        foreach (ServiceRequest::where('user_id', $id)->orWhere('email', $customer->email)->latest()->take(20)->get() as $r) {
            $push($r->created_at, 'service_request', ($r->subject ?? 'Service request') . ' — ' . ($r->request_number ?? ''), (string) ($r->review_status ?? $r->status), (string) ($r->request_number ?? ''), null);
        }
        foreach (Quotation::where('customer_id', $id)->latest()->take(20)->get() as $r) {
            $push($r->created_at, 'quotation', 'Quotation total ' . $r->total . ' ' . ($r->currency ?? ''), (string) $r->status, (string) ($r->quotation_number ?? ''), $link($portal ? 'portal.quotations.show' : 'admin.quotations.show', $r->id));
        }
        foreach (ServiceOrder::where('customer_id', $id)->latest()->take(20)->get() as $r) {
            $push($r->created_at, 'order', 'Order ' . $r->order_number, (string) $r->status, (string) $r->order_number, $link($rq, $r->id));
        }
        foreach (Invoice::where('customer_id', $id)->latest()->take(20)->get() as $r) {
            $push($r->created_at, 'invoice', 'Invoice total ' . $r->total . ' ' . ($r->currency ?? ''), (string) $r->status, (string) ($r->invoice_number ?? ''), $link($portal ? 'portal.invoices.show' : 'admin.invoices.show', $r->id));
        }
        foreach (Payment::where('customer_id', $id)->latest()->take(20)->get() as $r) {
            $push($r->created_at, 'payment', 'Payment ' . $r->amount . ' ' . ($r->currency ?? '') . ' via ' . ($r->payment_method ?? '—'), (string) $r->status, (string) ($r->payment_number ?? $r->transaction_id ?? ''), null);
        }
        foreach (Wallet::where('user_id', $id)->with(['transactions' => fn ($q) => $q->latest()->take(20)])->get() as $w) {
            foreach ($w->transactions as $t) {
                $push($t->created_at, 'wallet_' . ($t->type ?? 'entry'), ($t->description ?? 'Wallet movement') . ' (' . ($t->transaction_reference ?? '') . ')', (string) ($t->status ?? ''), (string) ($t->transaction_reference ?? ''), null);
            }
        }
        foreach (Project::where('customer_id', $id)->latest()->take(20)->get() as $r) {
            $push($r->created_at, 'project', 'Project — ' . ($r->name ?? ''), (string) $r->status, (string) ($r->project_number ?? ''), $link($portal ? 'portal.projects.show' : 'admin.projects.show', $r->id));
        }
        foreach (Ticket::where('customer_id', $id)->latest()->take(20)->get() as $r) {
            $push($r->created_at, 'ticket', 'Ticket — ' . ($r->subject ?? ''), (string) $r->status, (string) ($r->ticket_number ?? ''), $link($portal ? 'portal.tickets.show' : 'admin.tickets.show', $r->id));
        }
        // Own audit trail: logins, password changes, profile edits (metadata only).
        foreach (AuditLog::where('user_id', $id)->latest()->take(40)->get() as $log) {
            $push($log->created_at, 'activity', (string) ($log->description ?? $log->action), null, $log->auditable_id ? class_basename((string) $log->auditable_type) . ' #' . $log->auditable_id : null, null);
        }

        return $rows->sortByDesc('at')->take($limit)->values();
    }

    /** Status history of any record from the audit log (who/what/when). */
    public static function statusHistory(string $modelClass, int $modelId): Collection
    {
        return AuditLog::with('user')
            ->where('action', 'status.changed')
            ->where('auditable_type', $modelClass)
            ->where('auditable_id', $modelId)
            ->latest()->take(50)->get();
    }

    // ── Employee work history ──────────────────────────────────
    public static function employeeOverview(User $employee): array
    {
        $id = $employee->id;
        $tasks = Task::where('assigned_to', $id);
        $period = now()->startOfMonth();
        return [
            'tasks_assigned' => (clone $tasks)->count(),
            'tasks_completed' => (clone $tasks)->where('status', 'completed')->count(),
            'tasks_in_progress' => (clone $tasks)->where('status', 'in_progress')->count(),
            'tasks_this_month' => Task::where('assigned_to', $id)->where('created_at', '>=', $period)->count(),
            'completed_this_month' => Task::where('assigned_to', $id)->where('status', 'completed')->where('updated_at', '>=', $period)->count(),
            'projects_managed' => Project::where('project_manager_id', $id)->count(),
            'projects_involved' => Project::whereHas('members', fn ($q) => $q->where('user_id', $id))->count() + Project::where('project_manager_id', $id)->count(),
            'tickets_assigned' => Ticket::where('assigned_to', $id)->count(),
            'tickets_resolved' => Ticket::where('assigned_to', $id)->whereIn('status', ['resolved', 'closed'])->count(),
            'task_comments' => TaskComment::where('user_id', $id)->count(),
            'ticket_messages' => TicketMessage::where('user_id', $id)->count(),
            'recorded_actions' => AuditLog::where('user_id', $id)->count(),
        ];
    }

    public static function employeeTimeline(User $employee, int $limit = 100): Collection
    {
        $id = $employee->id;
        $events = collect();
        foreach (Task::where('assigned_to', $id)->latest()->take(30)->get() as $t) {
            $events->push(['at' => $t->created_at, 'label' => 'Task assigned', 'detail' => $t->title . ' · ' . $t->status, 'url' => route('admin.tasks.show', $t)]);
        }
        foreach (TaskComment::with('task')->where('user_id', $id)->latest()->take(30)->get() as $c) {
            $events->push(['at' => $c->created_at, 'label' => 'Work update posted', 'detail' => ($c->task?->title ?? 'Task') . ': ' . mb_substr($c->comment ?? '', 0, 90), 'url' => $c->task ? route('admin.tasks.show', $c->task) : null]);
        }
        foreach (AuditLog::where('user_id', $id)->latest()->take(40)->get() as $log) {
            $events->push(['at' => $log->created_at, 'label' => $log->description ?? $log->action, 'detail' => $log->module . ($log->auditable_type ? ' · ' . class_basename($log->auditable_type) . ' #' . $log->auditable_id : ''), 'url' => null]);
        }
        return $events->sortByDesc('at')->take($limit)->values();
    }

    /** Employee → customer → service links via shared tasks/projects/tickets. */
    public static function employeeCustomerLinks(User $employee): Collection
    {
        $id = $employee->id;
        $links = collect();
        // Assigned tasks AND multi-employee contributions, resolved through
        // project, direct customer, or service order (orders carry tasks
        // without a project row — those links must not be lost).
        $taskIds = Task::where('assigned_to', $id)->pluck('id')
            ->merge(\App\Models\TaskContributor::where('user_id', $id)->pluck('task_id'))
            ->unique();
        $tasks = Task::with(['project.customer', 'customer', 'serviceOrder.customer'])->whereIn('id', $taskIds)->latest()->take(30)->get();
        foreach ($tasks as $t) {
            $customer = $t->project?->customer ?? $t->customer ?? $t->serviceOrder?->customer;
            if (!$customer) continue;
            $links->push(['customer' => $customer, 'via' => 'Task: ' . $t->title, 'project' => $t->project, 'at' => $t->created_at]);
        }
        $tickets = Ticket::with('customer')->where('assigned_to', $id)->latest()->take(20)->get();
        foreach ($tickets as $t) {
            if (!$t->customer) continue;
            $links->push(['customer' => $t->customer, 'via' => 'Ticket: ' . $t->ticket_number, 'project' => null, 'at' => $t->created_at]);
        }
        return $links->sortByDesc('at')->take(50)->values();
    }

    // ── Global search ──────────────────────────────────────────
    public static function globalSearch(string $q): array
    {
        $q = addcslashes(mb_substr(trim($q), 0, 100), '%_\\');
        if ($q === '') return ['customers' => [], 'employees' => [], 'references' => []];
        $like = "%{$q}%";

        $customers = User::where('role', 'customer')->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('company_name', 'like', $like))->take(10)->get();
        $employees = User::staff()->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like))->take(10)->get();

        $refs = [];
        $find = function ($model, $column, $label, $route, $withParam = true) use ($like, &$refs) {
            foreach ($model::where($column, 'like', $like)->take(5)->get() as $row) {
                $refs[] = ['label' => $label, 'ref' => $row->$column, 'url' => $withParam ? route($route, $row) : route($route)];
            }
        };
        $find(Ticket::class, 'ticket_number', 'Ticket', 'admin.tickets.show');
        $find(Invoice::class, 'invoice_number', 'Invoice', 'admin.invoices.show');
        $find(ServiceOrder::class, 'order_number', 'Order', 'admin.work-orders.show');
        $find(Project::class, 'project_number', 'Project', 'admin.projects.show');
        $find(Task::class, 'task_number', 'Task', 'admin.tasks.show');
        $find(Payment::class, 'payment_number', 'Payment', 'admin.payments.index', false);
        $find(Quotation::class, 'quotation_number', 'Quotation', 'admin.quotations.show');

        return ['customers' => $customers, 'employees' => $employees, 'references' => $refs];
    }

    // ── Consistency checks (read-only) ─────────────────────────
    public static function consistencyCheck(): array
    {
        $issues = [];
        $push = function (string $key, string $label, $query, callable $describe) use (&$issues) {
            $rows = $query->take(20)->get();
            if ($rows->isNotEmpty()) {
                $issues[$key] = ['label' => $label, 'count' => $query->count(), 'samples' => $rows->map($describe)->all()];
            }
        };

        $push('orders_no_customer', 'Orders with missing customer', ServiceOrder::whereDoesntHave('customer'), fn ($r) => $r->order_number);
        $push('projects_no_customer', 'Projects with missing customer', Project::whereDoesntHave('customer'), fn ($r) => $r->project_number);
        $push('invoices_no_customer', 'Invoices with missing customer', Invoice::whereDoesntHave('customer'), fn ($r) => $r->invoice_number);
        $push('invoice_math', 'Invoices where paid + due ≠ total', Invoice::whereRaw('ROUND(amount_paid + amount_due, 2) != ROUND(total, 2)'), fn ($r) => $r->invoice_number . " (paid {$r->amount_paid} + due {$r->amount_due} ≠ {$r->total})");
        $push('tickets_inactive_assignee', 'Tickets assigned to inactive users', Ticket::whereHas('assignee', fn ($q) => $q->where('is_active', false)), fn ($r) => $r->ticket_number);
        $push('tasks_inactive_assignee', 'Tasks assigned to inactive users', Task::whereHas('assignee', fn ($q) => $q->where('is_active', false)), fn ($r) => $r->task_number);
        $push('payments_over_invoice', 'Payments exceeding their invoice total', Payment::whereHas('invoice', fn ($q) => $q->whereColumn('payments.amount', '>', 'invoices.total')), fn ($r) => $r->payment_number);

        return $issues;
    }
}
