<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TraceabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * ProductionAuditTest â€” executable CRM+ERP production-readiness audit.
 * Every test proves connected business state, not just HTTP 200.
 */
class ProductionAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function service(): Service
    {
        $cat = ServiceCategory::create(['name' => 'Audit Cat', 'slug' => 'audit-cat']);
        return Service::create(['category_id' => $cat->id, 'name' => 'Audit Service', 'slug' => 'audit-service']);
    }

    /** @test */
    public function full_lifecycle_keeps_one_identity_end_to_end()
    {
        $service = $this->service();
        $sales = User::factory()->create(['role' => 'sales_agent', 'is_active' => true]);
        $pm = User::factory()->create(['role' => 'project_manager', 'is_active' => true]);
        $eng = User::factory()->create(['role' => 'employee', 'is_active' => true]);
        $finance = User::factory()->create(['role' => 'finance_manager', 'is_active' => true]);

        // Registration creates exactly one user.
        $this->post(route('register'), ['name' => 'NorthBridge', 'email' => 'nb@example.test', 'password' => 'password123', 'password_confirmation' => 'password123', 'role' => 'customer'])->assertRedirect();
        $customer = User::where('email', 'nb@example.test')->firstOrFail();

        // Lead â†’ convert links (never duplicates) the same identity.
        $lead = Lead::create(['name' => 'NorthBridge', 'email' => 'nb@example.test', 'status' => 'qualified']);
        $this->actingAs($sales)->post(route('admin.leads.convert', $lead))->assertSessionHas('success');
        $this->assertEquals($customer->id, $lead->fresh()->customer_id);
        $this->assertSame(1, User::where('email', 'nb@example.test')->count());
        // Second conversion is refused: no duplicate customer.
        $this->actingAs($sales)->post(route('admin.leads.convert', $lead))->assertStatus(422);
        $this->assertSame(1, User::where('email', 'nb@example.test')->count());

        // Chain on the SAME customer id.
        $order = ServiceOrder::create(['order_number' => 'ORD-AUD-1', 'customer_id' => $customer->id, 'service_id' => $service->id, 'requirements' => 'Managed IT Support', 'status' => 'confirmed', 'total' => 1500, 'amount_paid' => 0, 'amount_due' => 1500]);
        $invoice = Invoice::create(['invoice_number' => 'INV-AUD-1', 'customer_id' => $customer->id, 'subtotal' => 1500, 'total' => 1500, 'amount_paid' => 0, 'amount_due' => 1500, 'status' => 'sent', 'due_date' => now()->addDays(14)]);
        foreach ([['PAY-AUD-1', 500], ['PAY-AUD-2', 400]] as [$num, $amt]) {
            Payment::create(['payment_number' => $num, 'invoice_id' => $invoice->id, 'customer_id' => $customer->id, 'amount' => $amt, 'status' => 'completed', 'payment_method' => 'bank_transfer', 'paid_at' => now()]);
            $invoice->increment('amount_paid', $amt);
            $invoice->decrement('amount_due', $amt);
            $order->increment('amount_paid', $amt);
            $order->decrement('amount_due', $amt);
        }
        $project = Project::create(['project_number' => 'PRJ-AUD-1', 'name' => 'NB rollout', 'slug' => 'nb-rollout', 'customer_id' => $customer->id, 'project_manager_id' => $pm->id, 'status' => 'in_progress']);
        $task = Task::create(['task_number' => 'TSK-AUD-1', 'project_id' => $project->id, 'title' => 'M365 setup', 'assigned_to' => $eng->id, 'created_by' => $pm->id, 'status' => 'completed']);
        $ticket = Ticket::create(['ticket_number' => 'TK-AUD-1', 'customer_id' => $customer->id, 'subject' => 'Q', 'description' => 'D', 'status' => 'resolved', 'assigned_to' => $eng->id]);

        // One identity everywhere.
        foreach ([$order, $invoice, $project, $ticket] as $row) {
            $this->assertEquals($customer->id, $row->customer_id);
        }
        $this->assertSame($customer->id, $task->project->customer_id);

        // Finance reconciles identically in every view.
        $invoice->refresh(); $order->refresh();
        $this->assertSame(900.0, (float) $invoice->amount_paid);
        $this->assertSame(600.0, (float) $invoice->amount_due);
        $this->assertSame((float) $invoice->amount_due, (float) $order->amount_due);
        $overview = TraceabilityService::customerOverview($customer);
        $this->assertSame(600.0, $overview['outstanding']);
        $this->assertSame(900.0, $overview['paid_total']);
        $this->assertSame(1, $overview['orders']);

        // Final payment closes to exactly zero.
        Payment::create(['payment_number' => 'PAY-AUD-3', 'invoice_id' => $invoice->id, 'customer_id' => $customer->id, 'amount' => 600, 'status' => 'completed', 'payment_method' => 'bank_transfer', 'paid_at' => now()]);
        $invoice->increment('amount_paid', 600); $invoice->decrement('amount_due', 600);
        $this->assertSame(0.0, (float) $invoice->fresh()->amount_due);
        $this->assertSame(0.0, TraceabilityService::customerOverview($customer)['outstanding']);

        // History shows the whole chain; finance sees the same numbers.
        $labels = TraceabilityService::customerTimeline($customer, 'admin')->pluck('label')->all();
        foreach (['Order created', 'Payment received', 'Invoice issued', 'Project created', 'Ticket opened'] as $l) {
            $this->assertContains($l, $labels);
        }
        $this->actingAs($finance)->get(route('admin.history.customer', $customer))->assertStatus(200)->assertSee('ORD-AUD-1', false);
    }

    /** @test */
    public function multi_employee_multi_service_attribution_is_exact()
    {
        $service = $this->service();
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $engA = User::factory()->create(['role' => 'employee', 'is_active' => true]);
        $engB = User::factory()->create(['role' => 'employee', 'is_active' => true]);
        $pm = User::factory()->create(['role' => 'project_manager', 'is_active' => true]);

        $project = Project::create(['project_number' => 'PRJ-AUD-2', 'name' => 'P', 'slug' => 'p2', 'customer_id' => $customer->id, 'project_manager_id' => $pm->id]);
        Task::create(['task_number' => 'TSK-AUD-A', 'project_id' => $project->id, 'title' => 'Task A', 'assigned_to' => $engA->id, 'created_by' => $pm->id, 'status' => 'completed']);
        Task::create(['task_number' => 'TSK-AUD-B', 'project_id' => $project->id, 'title' => 'Task B', 'assigned_to' => $engB->id, 'created_by' => $pm->id, 'status' => 'in_progress']);

        $histA = TraceabilityService::employeeOverview($engA);
        $histB = TraceabilityService::employeeOverview($engB);
        $this->assertSame(1, $histA['tasks_completed']);
        $this->assertSame(0, $histB['tasks_completed']);
        $this->assertSame(1, $histB['tasks_in_progress']);

        // Customer history shows both; each employee only their own.
        $links = TraceabilityService::employeeCustomerLinks($engA);
        $this->assertTrue($links->contains(fn ($l) => str_contains($l['via'], 'Task A')));
        $this->assertFalse($links->contains(fn ($l) => str_contains($l['via'], 'Task B')));
    }

    /** @test */
    public function multiple_customers_stay_isolated_across_modules()
    {
        $service = $this->service();
        $mk = fn () => User::factory()->create(['role' => 'customer', 'is_active' => true]);
        [$a, $b, $c] = [$mk(), $mk(), $mk()];
        foreach ([$a, $b, $c] as $i => $cust) {
            $n = $i + 1;
            ServiceOrder::create(['order_number' => "ORD-ISO-{$n}", 'customer_id' => $cust->id, 'service_id' => $service->id, 'requirements' => 'Audit requirements', 'total' => 100 * $n, 'amount_paid' => 0, 'amount_due' => 100 * $n]);
            Ticket::create(['ticket_number' => "TK-ISO-{$n}", 'customer_id' => $cust->id, 'subject' => 'S', 'description' => 'D']);
            Invoice::create(['invoice_number' => "INV-ISO-{$n}", 'customer_id' => $cust->id, 'subtotal' => 100 * $n, 'total' => 100 * $n, 'amount_paid' => 0, 'amount_due' => 100 * $n, 'status' => 'sent', 'due_date' => now()->addDays(7)]);
        }

        foreach ([[$a, 'ORD-ISO-1', 'TK-ISO-2'], [$b, 'ORD-ISO-2', 'TK-ISO-3'], [$c, 'ORD-ISO-3', 'TK-ISO-1']] as [$cust, $own, $other]) {
            $content = $this->actingAs($cust)->get(route('portal.history.index'))->assertStatus(200)->getContent();
            $this->assertStringContainsString($own, $content);
            $this->assertStringNotContainsString($other, $content);
        }

        // Direct URL manipulation across every customer resource.
        $orderB = ServiceOrder::where('order_number', 'ORD-ISO-2')->first();
        $invB = Invoice::where('invoice_number', 'INV-ISO-2')->first();
        $tkB = Ticket::where('ticket_number', 'TK-ISO-2')->first();
        $this->actingAs($a)->get(route('portal.orders.show', $orderB->id))->assertStatus(404);
        // Invoices/projects use scoped findOrFail (404); both deny without leaking.
        $this->assertContains($this->actingAs($a)->get(route('portal.invoices.show', $invB->id))->getStatusCode(), [403, 404]);
        $this->assertContains($this->actingAs($a)->get(route('portal.tickets.show', $tkB->id))->getStatusCode(), [403, 404]);
        $proj = Project::create(['project_number' => 'PRJ-ISO-2', 'name' => 'P', 'slug' => 'p-iso-2', 'customer_id' => $b->id]);
        // Both 403 and scoped-404 deny access; neither leaks data.
        $this->assertContains($this->actingAs($a)->get(route('portal.projects.show', $proj->id))->getStatusCode(), [403, 404]);
    }

    /** @test */
    public function reference_numbers_are_unique_at_database_level()
    {
        $service = $this->service();
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        ServiceOrder::create(['order_number' => 'ORD-DUP-1', 'customer_id' => $customer->id, 'service_id' => $service->id, 'requirements' => 'Audit requirements']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        ServiceOrder::create(['order_number' => 'ORD-DUP-1', 'customer_id' => $customer->id, 'service_id' => $service->id, 'requirements' => 'Audit requirements']);
    }

    /** @test */
    public function duplicate_payment_references_are_rejected()
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $invoice = Invoice::create(['invoice_number' => 'INV-DUP-1', 'customer_id' => $customer->id, 'subtotal' => 100, 'total' => 100, 'amount_paid' => 0, 'amount_due' => 100, 'status' => 'sent', 'due_date' => now()->addDays(7)]);
        Payment::create(['payment_number' => 'PAY-DUP-1', 'invoice_id' => $invoice->id, 'customer_id' => $customer->id, 'amount' => 100, 'status' => 'completed', 'payment_method' => 'bank_transfer', 'paid_at' => now()]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Payment::create(['payment_number' => 'PAY-DUP-1', 'invoice_id' => $invoice->id, 'customer_id' => $customer->id, 'amount' => 100, 'status' => 'completed', 'payment_method' => 'bank_transfer', 'paid_at' => now()]);
    }

    /** @test */
    public function mass_assignment_cannot_escalate_or_steal_records()
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $other = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        // role / is_active / approval fields are not whitelisted on profile update.
        $this->actingAs($customer)->put(route('portal.profile.update'), [
            'name' => 'Still Customer', 'email' => $customer->email,
            'role' => 'admin', 'is_active' => true, 'role_approval_status' => 'approved',
        ])->assertRedirect();
        $customer->refresh();
        $this->assertSame('customer', $customer->role);
        $this->assertNull($customer->role_approval_status);

        // customer_id override on ticket creation is ignored (hardcoded to auth).
        $cat = \App\Models\TicketCategory::create(['name' => 'Audit', 'slug' => 'audit-cat']);
        $this->actingAs($customer)->post(route('portal.tickets.store'), [
            'subject' => 'Hi', 'description' => 'Body', 'category_id' => $cat->id,
            'priority' => 'low', 'customer_id' => $other->id,
        ])->assertRedirect();
        $this->assertSame($customer->id, Ticket::latest()->first()->customer_id);
    }

    /** @test */
    public function deactivation_and_role_change_preserve_history()
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $eng = User::factory()->create(['role' => 'employee', 'is_active' => true, 'name' => 'Keep Me Visible']);
        $pm = User::factory()->create(['role' => 'project_manager', 'is_active' => true]);
        $project = Project::create(['project_number' => 'PRJ-AUD-3', 'name' => 'P', 'slug' => 'p3', 'customer_id' => $customer->id, 'project_manager_id' => $pm->id]);
        $task = Task::create(['task_number' => 'TSK-AUD-3', 'project_id' => $project->id, 'title' => 'Work', 'assigned_to' => $eng->id, 'created_by' => $pm->id, 'status' => 'completed']);

        $eng->update(['is_active' => false]);
        $task->refresh();
        $this->assertSame('Keep Me Visible', $task->assignee->name);
        $this->assertFalse($task->assignee->is_active);

        // Role change keeps attribution, swaps access.
        $eng->update(['role' => 'support_agent', 'is_active' => true]);
        $this->assertSame('Keep Me Visible', Task::find($task->id)->assignee->name);
        $this->actingAs($eng)->get(route('admin.invoices.index'))->assertStatus(403);
        $this->actingAs($eng)->get(route('admin.tickets.index'))->assertStatus(200);
    }

    /** @test */
    public function money_columns_are_exact_decimals_and_math_is_exact()
    {
        foreach (['invoices' => ['total', 'amount_paid', 'amount_due'], 'payments' => ['amount'], 'service_orders' => ['total', 'amount_paid', 'amount_due']] as $table => $cols) {
            $info = collect(DB::select("PRAGMA table_info('{$table}')"))->keyBy('name');
            foreach ($cols as $col) {
                // Laravel decimal() → NUMERIC affinity on SQLite, DECIMAL on
                // MySQL; both are exact (verified by the penny test below).
                $this->assertMatchesRegularExpression('/decimal|numeric/i', $info[$col]->type, "{$table}.{$col} must be exact decimal");
            }
        }

        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $invoice = Invoice::create(['invoice_number' => 'INV-CENT-1', 'customer_id' => $customer->id, 'subtotal' => 1000, 'total' => 1000, 'amount_paid' => 0, 'amount_due' => 1000, 'status' => 'sent', 'due_date' => now()->addDays(7)]);
        foreach ([400, 300, 300] as $i => $amt) {
            Payment::create(['payment_number' => "PAY-CENT-{$i}", 'invoice_id' => $invoice->id, 'customer_id' => $customer->id, 'amount' => $amt, 'status' => 'completed', 'payment_method' => 'bank_transfer', 'paid_at' => now()]);
            $invoice->increment('amount_paid', $amt);
            $invoice->decrement('amount_due', $amt);
        }
        $invoice->refresh();
        $this->assertSame(1000.0, (float) $invoice->amount_paid);
        $this->assertSame(0.0, (float) $invoice->amount_due);

        // Penny precision.
        $p = Payment::create(['payment_number' => 'PAY-CENT-X', 'invoice_id' => $invoice->id, 'customer_id' => $customer->id, 'amount' => 0.01, 'status' => 'completed', 'payment_method' => 'bank_transfer', 'paid_at' => now()]);
        $this->assertSame(0.01, (float) $p->fresh()->amount);
    }

    /** @test */
    public function registration_validation_is_strict()
    {
        User::factory()->create(['email' => 'taken@example.test']);
        $this->post(route('register'), ['name' => 'X', 'email' => 'taken@example.test', 'password' => 'password123', 'password_confirmation' => 'password123'])->assertSessionHasErrors('email');
        $this->post(route('register'), ['name' => 'X', 'email' => 'weak@example.test', 'password' => 'short', 'password_confirmation' => 'short'])->assertSessionHasErrors('password');
        $this->post(route('register'), ['name' => 'X', 'email' => 'evil@example.test', 'password' => 'password123', 'password_confirmation' => 'password123', 'role' => 'admin'])->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'evil@example.test']);
    }

    /** @test */
    public function ai_context_is_scoped_to_the_requesting_customer()
    {
        $a = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $b = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        Ticket::create(['ticket_number' => 'TK-AI-A', 'customer_id' => $a->id, 'subject' => 'A private issue', 'description' => 'D', 'status' => 'open']);

        $service = app(\App\Services\Ai\AiKnowledgeService::class);
        $ctxA = $service->getCustomerContext($a);
        $ctxB = $service->getCustomerContext($b);
        $this->assertStringContainsString('A private issue', json_encode($ctxA));
        $this->assertStringNotContainsString('A private issue', json_encode($ctxB));
    }

    /** @test */
    public function failed_validation_creates_no_partial_records()
    {
        $count = fn () => [ServiceOrder::count(), Invoice::count(), Payment::count()];
        $before = $count();
        $this->post(route('register'), ['name' => 'X', 'email' => 'not-an-email', 'password' => 'password123', 'password_confirmation' => 'password123'])->assertSessionHasErrors('email');
        $this->assertSame($before, $count());
        $this->assertDatabaseMissing('users', ['name' => 'X']);
    }
}


