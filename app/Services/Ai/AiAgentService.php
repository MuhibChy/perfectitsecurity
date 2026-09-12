<?php

namespace App\Services\Ai;

use App\Models\AiConversation;
use App\Models\AiEscalation;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Contract;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\ServiceRequest;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketMessage;
use App\Models\Task;
use App\Models\User;
use App\Services\SlaService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * AiAgentService — controlled backend tools for the AI Customer & Service
 * Management Agent.
 *
 * The language model NEVER touches the database directly. It only receives
 * context assembled here, and consequential actions run through these
 * explicitly-authorized methods. Every method enforces role checks
 * server-side and writes an audit entry (metadata only — never prompt
 * content, secrets, or other customers' data).
 *
 * Tool inventory (spec §3):
 *  get_authenticated_customer, get_customer_profile, get_customer_orders,
 *  get_customer_tickets, get_customer_projects, get_customer_contracts,
 *  get_customer_invoice_status, search_services, search_knowledge_base,
 *  search_public_company_information, create_support_ticket,
 *  create_service_request, create_quote_request, add_ticket_message,
 *  get_ticket_status, get_order_status, get_project_status,
 *  escalate_to_employee (+ employee: get_assigned_tickets/tasks,
 *  summarize_customer_issues).
 */
class AiAgentService
{
    private AiKnowledgeService $knowledge;

    public function __construct()
    {
        $this->knowledge = app(AiKnowledgeService::class);
    }

    // ------------------------------------------------------------ identity
    public function getAuthenticatedCustomer(?User $user): ?User
    {
        return ($user && $user->isCustomer()) ? $user : null;
    }

    public function requireCustomer(?User $user): User
    {
        $customer = $this->getAuthenticatedCustomer($user);
        abort_unless($customer, 401, 'Please log in to your customer account first.');
        return $customer;
    }

    public function getCustomerProfile(User $customer): array
    {
        $this->assertOwnCustomer($customer, $customer);
        return [
            'name' => $customer->name,
            'email' => $customer->email,
            'company' => $customer->company?->name,
            'open_tickets' => Ticket::where('customer_id', $customer->id)->open()->count(),
            'active_orders' => ServiceOrder::where('customer_id', $customer->id)
                ->whereNotIn('status', ['closed', 'cancelled'])->count(),
            'pending_invoices' => Invoice::where('customer_id', $customer->id)
                ->whereIn('status', ['sent', 'viewed', 'overdue', 'partially_paid'])->count(),
        ];
    }

    // ------------------------------------------------------- status readers
    /** Own open tickets (metadata only — never internal notes). */
    public function getCustomerTickets(User $customer, int $limit = 5): array
    {
        return Ticket::where('customer_id', $customer->id)->latest()->limit($limit)->get()
            ->map(fn ($t) => [
                'number' => $t->ticket_number, 'subject' => $t->subject,
                'status' => $t->status, 'priority' => $t->priority,
                'sla' => app(SlaService::class)->getSlaStatus($t),
            ])->all();
    }

    public function getCustomerOrders(User $customer, int $limit = 5): array
    {
        return ServiceOrder::where('customer_id', $customer->id)->latest()->limit($limit)->get()
            ->map(fn ($o) => [
                'number' => $o->order_number, 'service' => $o->service?->name,
                'status' => $o->status, 'total' => $o->total,
                'paid' => $o->amount_paid, 'due' => $o->amount_due,
            ])->all();
    }

    public function getCustomerProjects(User $customer, int $limit = 5): array
    {
        return Project::where('customer_id', $customer->id)->latest()->limit($limit)->get()
            ->map(fn ($p) => [
                'number' => $p->project_number, 'name' => $p->name,
                'status' => $p->status, 'progress' => $p->progress,
            ])->all();
    }

    public function getCustomerContracts(User $customer, int $limit = 5): array
    {
        return Contract::where('customer_id', $customer->id)->latest()->limit($limit)->get()
            ->map(fn ($c) => [
                'number' => $c->contract_number, 'title' => $c->title,
                'status' => $c->status, 'start' => $c->start_date, 'end' => $c->end_date,
            ])->all();
    }

    public function getCustomerInvoiceStatus(User $customer, int $limit = 5): array
    {
        return Invoice::where('customer_id', $customer->id)->latest()->limit($limit)->get()
            ->map(fn ($i) => [
                'number' => $i->invoice_number, 'total' => $i->total,
                'paid' => $i->amount_paid, 'due' => $i->amount_due, 'status' => $i->status,
            ])->all();
    }

    public function getTicketStatus(User $customer, string $ticketNumber): ?array
    {
        $t = Ticket::where('customer_id', $customer->id)->where('ticket_number', $ticketNumber)->first();
        if (!$t) return null;
        return ['number' => $t->ticket_number, 'subject' => $t->subject, 'status' => $t->status,
            'priority' => $t->priority, 'sla' => app(SlaService::class)->getSlaStatus($t)];
    }

    public function getOrderStatus(User $customer, string $orderNumber): ?array
    {
        $o = ServiceOrder::where('customer_id', $customer->id)->where('order_number', $orderNumber)->first();
        if (!$o) return null;
        return ['number' => $o->order_number, 'service' => $o->service?->name, 'status' => $o->status,
            'total' => $o->total, 'paid' => $o->amount_paid, 'due' => $o->amount_due];
    }

    public function getProjectStatus(User $customer, string $projectNumber): ?array
    {
        $p = Project::where('customer_id', $customer->id)->where('project_number', $projectNumber)->first();
        if (!$p) return null;
        return ['number' => $p->project_number, 'name' => $p->name, 'status' => $p->status, 'progress' => $p->progress];
    }

    // ------------------------------------------------------------- search
    public function searchServices(string $query, int $limit = 6): array
    {
        $safe = addcslashes(mb_substr($query, 0, 120), '\\%_');
        return Service::where('is_active', true)
            ->where(function ($q) use ($safe) {
                $q->where('name', 'like', "%{$safe}%")->orWhere('short_description', 'like', "%{$safe}%");
            })->with('category')->limit($limit)->get()
            ->map(fn ($s) => ['name' => $s->name, 'category' => $s->category?->name,
                'price_type' => $s->price_type, 'starting_price' => $s->starting_price])->all();
    }

    public function searchKnowledgeBase(string $query, ?User $user, int $limit = 5): array
    {
        return $this->knowledge->searchRelevantArticles($query, $user, $limit);
    }

    // ------------------------------------------------------------ creation
    protected function validate(array $data, array $rules): array
    {
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        return $validator->validated();
    }

    /** Customer-owned ticket with SLA + audit. Never assigns critical incidents to arbitrary staff. */
    public function createSupportTicket(User $customer, array $data): Ticket
    {
        $v = $this->validate($data, [
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category' => 'nullable|string|max:100',
            'priority' => 'nullable|in:low,medium,high,urgent,critical',
        ]);
        $category = null;
        if (!empty($v['category'])) {
            $category = TicketCategory::where('name', 'like', "%{$v['category']}%")->first();
        }
        $ticket = Ticket::create([
            'customer_id' => $customer->id,
            'subject' => $v['subject'],
            'description' => $v['description'],
            'category_id' => $category?->id,
            'priority' => $v['priority'] ?? 'medium',
            'status' => 'new',
        ]);
        app(SlaService::class)->applySla($ticket);
        AuditLog::log('ai.ticket_created', 'tickets', $ticket, "AI-agent ticket {$ticket->ticket_number} for customer {$customer->id}.");
        return $ticket;
    }

    /** Customer-owned service request (+ CRM lead). Amounts/pricing never accepted from chat. */
    public function createServiceRequest(User $customer, array $data): ServiceRequest
    {
        $v = $this->validate($data, [
            'service_id' => 'nullable|exists:services,id',
            'requirements' => 'required|string|min:10|max:5000',
        ]);
        $sr = ServiceRequest::create([
            'user_id' => $customer->id,
            'service_id' => $v['service_id'] ?? null,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'subject' => 'AI Assistant Service Request',
            'requirements' => $v['requirements'],
            'lead_source' => 'ai-assistant',
            'status' => 'new',
            'review_status' => 'new',
        ]);
        \App\Models\Lead::create([
            'service_request_id' => $sr->id, 'customer_id' => $customer->id,
            'name' => $customer->name, 'email' => $customer->email,
            'source' => 'ai-assistant', 'status' => 'new',
            'notes' => 'Created via AI agent after explicit confirmation.',
        ]);
        AuditLog::log('ai.service_request_created', 'service_requests', $sr, "AI-agent request for customer {$customer->id}.");
        return $sr;
    }

    /** Quote request = service request flagged for sales review + estimated interest only. */
    public function createQuoteRequest(User $customer, array $data): ServiceRequest
    {
        $v = $this->validate($data, [
            'service_id' => 'nullable|exists:services,id',
            'requirements' => 'required|string|min:10|max:5000',
            'budget_range' => 'nullable|string|max:50',
        ]);
        $sr = $this->createServiceRequest($customer, $data);
        $sr->update([
            'subject' => 'AI Assistant Quote Request',
            'budget_range' => $v['budget_range'] ?? null,
            'lead_source' => 'ai-quote',
        ]);
        AuditLog::log('ai.quote_request_created', 'service_requests', $sr, "AI-agent quote request for customer {$customer->id}.");
        return $sr->fresh();
    }

    /** Customer reply on their OWN open ticket only. Internal-note flag can never be set from chat. */
    public function addTicketMessage(User $customer, string $ticketNumber, string $message): TicketMessage
    {
        $ticket = Ticket::where('customer_id', $customer->id)->where('ticket_number', $ticketNumber)->first();
        abort_unless($ticket, 404, 'Ticket not found in your account.');
        abort_if(in_array($ticket->status, ['closed', 'cancelled'], true), 422, 'This ticket is closed; please open a new one.');
        $v = $this->validate(['message' => $message], ['message' => 'required|string|max:5000']);
        $msg = TicketMessage::create([
            'ticket_id' => $ticket->id, 'user_id' => $customer->id,
            'message' => $v['message'], 'is_internal_note' => false,
        ]);
        AuditLog::log('ai.ticket_reply', 'tickets', $ticket, "AI-agent customer reply on {$ticket->ticket_number}.");
        return $msg;
    }

    // ---------------------------------------------------------- escalation
    public function escalateToEmployee(AiConversation $conversation, ?User $user, string $reason): AiEscalation
    {
        $v = $this->validate(['reason' => $reason], ['reason' => 'required|string|max:500']);
        $conversation->escalate($v['reason']);
        $escalation = AiEscalation::create([
            'conversation_id' => $conversation->id,
            'reason' => $v['reason'],
            'context_summary' => mb_substr($conversation->messages()->latest()->limit(5)->get()->pluck('content')->join(' | '), 0, 1000),
            'status' => 'pending',
        ]);
        AuditLog::log('ai.escalated', 'ai', $conversation, 'AI conversation escalated to human support.');
        return $escalation;
    }

    // ---------------------------------------------------- employee workspace
    private function requireStaff(User $user): void
    {
        abort_unless($user->isStaff(), 403, 'Staff access required.');
    }

    /** Tickets assigned to the employee or unassigned (never other agents' private scope beyond role). */
    public function getAssignedTickets(User $employee, int $limit = 10): array
    {
        $this->requireStaff($employee);
        return Ticket::where(function ($q) use ($employee) {
            $q->where('assigned_to', $employee->id)->orWhereNull('assigned_to');
        })->open()->latest()->limit($limit)->get()
            ->map(fn ($t) => ['number' => $t->ticket_number, 'subject' => $t->subject,
                'status' => $t->status, 'priority' => $t->priority,
                'customer_id' => $t->customer_id])->all();
    }

    public function getAssignedTasks(User $employee, int $limit = 10): array
    {
        $this->requireStaff($employee);
        return Task::where('assigned_to', $employee->id)
            ->whereNotIn('status', ['completed', 'cancelled'])->latest()->limit($limit)->get()
            ->map(fn ($t) => ['number' => $t->task_number, 'title' => $t->title,
                'status' => $t->status, 'priority' => $t->priority])->all();
    }

    /** Authorized customer summary for staff: counts + recent subjects only, no unrelated customers. */
    public function summarizeCustomerIssues(User $employee, int $customerId): array
    {
        $this->requireStaff($employee);
        $customer = User::where('id', $customerId)->where('role', 'customer')->firstOrFail();
        $summary = [
            'customer' => $customer->name,
            'open_tickets' => Ticket::where('customer_id', $customer->id)->open()->count(),
            'recent_tickets' => Ticket::where('customer_id', $customer->id)->latest()->limit(5)->get()
                ->map(fn ($t) => ['number' => $t->ticket_number, 'subject' => $t->subject, 'status' => $t->status])->all(),
            'active_orders' => ServiceOrder::where('customer_id', $customer->id)->whereNotIn('status', ['closed', 'cancelled'])->count(),
            'pending_invoices' => Invoice::where('customer_id', $customer->id)->whereIn('status', ['sent', 'viewed', 'overdue', 'partially_paid'])->count(),
        ];
        AuditLog::log('ai.customer_summary', 'customers', $customer, "Staff {$employee->id} requested AI summary.");
        return $summary;
    }

    // ------------------------------------------------------- ownership guard
    private function assertOwnCustomer(User $actor, User $customer): void
    {
        abort_unless($actor->id === $customer->id || $actor->isStaff(), 403);
    }
}
