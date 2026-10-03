<?php

namespace Tests\Feature;

use App\Models\CashMemo;
use App\Models\Invoice;
use App\Models\OrderPaymentSchedule;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Receipt;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceEvent;
use App\Models\ServiceOrder;
use App\Models\Task;
use App\Models\User;
use App\Services\ServiceTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalStagedPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    /** @test */
    public function user_approval_workflow_is_authorized_and_audited()
    {
        $admin = $this->admin();
        $pending = User::factory()->create(['role' => 'customer', 'is_active' => false]);

        // Non-admin cannot approve.
        $staff = User::factory()->create(['role' => 'employee', 'is_active' => true]);
        $this->actingAs($staff)->post(route('admin.users.approve', $pending))->assertStatus(403);

        $this->actingAs($admin)->post(route('admin.users.approve', $pending), ['approval_note' => 'Verified business.'])->assertSessionHas('success');
        $pending->refresh();
        $this->assertTrue($pending->is_active);
        $this->assertSame($admin->id, (int) $pending->approved_by);
        $this->assertNotNull($pending->approved_at);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.approved', 'auditable_id' => $pending->id]);

        $this->actingAs($admin)->post(route('admin.users.suspend', $pending))->assertSessionHas('success');
        $this->assertFalse($pending->fresh()->is_active);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.suspended']);

        // Status filters work.
        $this->actingAs($admin)->get(route('admin.users.index', ['status' => 'suspended']))->assertStatus(200)->assertSee($pending->name, false);
    }

    /** @test */
    public function complete_staged_workflow_executes_and_reconciles()
    {
        $admin = $this->admin();
        $finance = User::factory()->create(['role' => 'finance_manager', 'is_active' => true]);
        $pm = User::factory()->create(['role' => 'project_manager', 'is_active' => true]);
        $eng = User::factory()->create(['role' => 'employee', 'is_active' => true]);
        $cat = ServiceCategory::create(['name' => 'Audit Cat', 'slug' => 'audit-cat-sp']);
        $service = Service::create(['category_id' => $cat->id, 'name' => '[TEST] Website Security Assessment', 'slug' => 'test-sec-assess']);

        // Registration + admin approval.
        $this->post(route('register'), ['name' => '[TEST] Customer Alpha', 'email' => 'alpha@example.test', 'password' => 'password123', 'password_confirmation' => 'password123', 'role' => 'customer'])->assertRedirect();
        $customer = User::where('email', 'alpha@example.test')->firstOrFail();
        $customer->update(['is_active' => false]);
        $this->actingAs($admin)->post(route('admin.users.approve', $customer))->assertSessionHas('success');
        $this->assertTrue($customer->fresh()->is_active);
        $customer->update(['email_verified_at' => now()]);
        $customer = $customer->fresh();

        // Order placement records the correct service + snapshot.
        $order = ServiceOrder::create([
            'order_number' => 'ORD-SP-1', 'customer_id' => $customer->id, 'service_id' => $service->id,
            'requirements' => 'Full assessment scope', 'status' => 'confirmed', 'total' => 1000,
            'amount_paid' => 0, 'amount_due' => 1000, 'currency' => 'USD',
        ]);
        Invoice::create(['invoice_number' => 'INV-SP-1', 'customer_id' => $customer->id, 'service_order_id' => $order->id, 'subtotal' => 1000, 'total' => 1000, 'amount_paid' => 0, 'amount_due' => 1000, 'status' => 'sent', 'due_date' => now()->addDays(14)]);
        $this->assertSame('[TEST] Website Security Assessment', $order->fresh()->service_snapshot['name']);

        // Order PDF carries true values.
        $pdf = $this->actingAs($finance)->get(route('admin.work-orders.pdf', $order))->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $pdf->headers->get('Content-Type'));

        // Payment schedule: advance 300 + stages 200/200 + final 300 = 1000.
        foreach ([['Advance', 300], ['Stage 1', 200], ['Stage 2', 200], ['Final', 300]] as [$title, $amt]) {
            $this->actingAs($finance)->post(route('admin.work-orders.schedule.store', $order), ['title' => $title, 'expected_amount' => $amt])->assertSessionHas('success');
        }
        $this->assertSame(1000.0, (float) OrderPaymentSchedule::where('order_id', $order->id)->sum('expected_amount'));
        // Overscheduling is refused.
        $this->actingAs($finance)->post(route('admin.work-orders.schedule.store', $order), ['title' => 'Extra', 'expected_amount' => 100])->assertSessionHas('error');

        $pay = function ($amount, $method = 'bank_transfer', $scheduleTitle = null) use ($finance, $order) {
            $payload = ['amount' => $amount, 'payment_method' => $method];
            if ($scheduleTitle) {
                $payload['schedule_id'] = OrderPaymentSchedule::where('order_id', $order->id)->where('title', $scheduleTitle)->firstOrFail()->id;
            }
            return $this->actingAs($finance)->post(route('admin.work-orders.record-payment', $order), $payload)->assertSessionHas('success');
        };

        // Advance 300 (cash → cash memo issued for the same payment).
        $pay(300, 'cash', 'Advance');
        $payment = Payment::where('service_order_id', $order->id)->firstOrFail();
        $this->assertDatabaseHas('cash_memos', ['payment_id' => $payment->id]);
        $memo = \App\Models\CashMemo::where('payment_id', $payment->id)->firstOrFail();
        $this->assertStringStartsWith('CM-', $memo->cash_memo_number);
        $this->actingAs($finance)->get(route('admin.cash-memos.pdf', $memo))->assertStatus(200);
        $this->actingAs($finance)->get(route('admin.receipts.pdf', $payment->receipt))->assertStatus(200);

        // Stage payments 200 + 200, final 300.
        $pay(200, 'bank_transfer', 'Stage 1');
        $pay(200, 'bank_transfer', 'Stage 2');
        $pay(300, 'bank_transfer', 'Final');

        $order->refresh();
        $this->assertSame(1000.0, (float) $order->amount_paid);
        $this->assertSame(0.0, (float) $order->amount_due);
        $invoice = $order->invoices()->first();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame(4, Payment::where('service_order_id', $order->id)->count());
        $this->assertSame(4, \App\Models\Receipt::where('service_order_id', $order->id)->count());
        // One money stream: income equals paid exactly once (no triple-count).
        $income = \App\Models\FinancialTransaction::where('type', 'income')->where('status', 'completed')->sum('amount');
        $this->assertSame(1000.0, round((float) $income, 2));

        // Schedule rows completed from linked payments.
        foreach (['Advance', 'Stage 1', 'Stage 2', 'Final'] as $title) {
            $row = OrderPaymentSchedule::where('order_id', $order->id)->where('title', $title)->firstOrFail();
            $this->assertSame('paid', $row->status);
        }

        // Duplicate payment blocked by outstanding math (due is 0).
        $this->actingAs($finance)->post(route('admin.work-orders.record-payment', $order), ['amount' => 100, 'payment_method' => 'cash'])->assertStatus(422);
        $this->assertSame(4, Payment::where('service_order_id', $order->id)->count());

        // Overpayment beyond total is rejected server-side.
        $this->actingAs($finance)->post(route('admin.work-orders.record-payment', $order), ['amount' => 1100, 'payment_method' => 'cash'])->assertStatus(422);

        // Service work → completion → customer confirmation → closure.
        $project = Project::create(['project_number' => 'PRJ-SP-1', 'name' => 'Alpha delivery', 'slug' => 'alpha-delivery', 'customer_id' => $customer->id, 'project_manager_id' => $pm->id, 'service_id' => $service->id, 'status' => 'in_progress']);
        $task = Task::create(['task_number' => 'TSK-SP-1', 'project_id' => $project->id, 'service_order_id' => $order->id, 'title' => 'Assessment', 'assigned_to' => $eng->id, 'created_by' => $pm->id, 'status' => 'completed', 'start_date' => now()->subDays(5), 'completed_at' => now()]);
        $order->update(['task_completed_at' => now(), 'status' => 'financially_completed']);
        $this->actingAs($customer)->post(route('portal.orders.confirm-completion', $order))->assertSessionHas('success');
        $this->assertDatabaseHas('service_events', ['order_id' => $order->id, 'action' => 'completed']);

        $this->actingAs($finance)->post(route('admin.work-orders.close', $order), ['closure_notes' => 'All stages verified.'])->assertSessionHas('success');
        $this->assertSame('closed', $order->fresh()->status);

        // Final service report + customer timeline show everything.
        $this->actingAs($finance)->get(route('admin.service.report-pdf', $project))->assertStatus(200);
        $timeline = ServiceTrackingService::serviceTimeline($order->id, null);
        $this->assertTrue($timeline->contains(fn ($e) => $e->action === 'completed'));

        // Maintenance linked after closure.
        $this->actingAs($pm)->post(route('admin.projects.maintenance.store', $project), [
            'title' => 'Quarterly review', 'type' => 'security review', 'next_due_at' => today()->addMonths(3)->toDateString(),
        ])->assertSessionHas('success');
    }

    /** @test */
    public function documents_are_isolated_and_value_accurate()
    {
        $a = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $b = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $cat = ServiceCategory::create(['name' => 'Audit Cat', 'slug' => 'audit-cat-doc']);
        $service = Service::create(['category_id' => $cat->id, 'name' => 'Doc Service', 'slug' => 'doc-service']);
        $makeOrder = function ($cust, $num) use ($service) {
            $order = ServiceOrder::create(['order_number' => $num, 'customer_id' => $cust->id, 'service_id' => $service->id, 'requirements' => 'Req', 'status' => 'confirmed', 'total' => 500, 'amount_paid' => 0, 'amount_due' => 500]);
            $invoice = Invoice::create(['invoice_number' => 'INV-' . $num, 'customer_id' => $cust->id, 'service_order_id' => $order->id, 'subtotal' => 500, 'total' => 500, 'amount_paid' => 0, 'amount_due' => 500, 'status' => 'sent', 'due_date' => now()->addDays(7)]);
            $payment = Payment::create(['payment_number' => 'PAY-' . $num, 'invoice_id' => $invoice->id, 'customer_id' => $cust->id, 'service_order_id' => $order->id, 'amount' => 500, 'status' => 'completed', 'payment_method' => 'cash', 'paid_at' => now()]);
            $receipt = \App\Models\Receipt::create(['payment_id' => $payment->id, 'invoice_id' => $invoice->id, 'customer_id' => $cust->id, 'service_order_id' => $order->id, 'amount' => 500, 'remaining_balance' => 0, 'currency' => 'USD', 'issued_at' => now()]);
            return compact('order', 'receipt');
        };
        $ra = $makeOrder($a, 'ORD-DOC-A');
        $rb = $makeOrder($b, 'ORD-DOC-B');

        // A cannot touch B's documents.
        $this->actingAs($a)->get(route('portal.orders.pdf', $rb['order']))->assertStatus(404);
        $this->actingAs($a)->get(route('portal.receipts.pdf', $rb['receipt']))->assertStatus(404);
        // Own documents open.
        $this->actingAs($a)->get(route('portal.orders.pdf', $ra['order']))->assertStatus(200);
        $this->actingAs($a)->get(route('portal.receipts.pdf', $ra['receipt']))->assertStatus(200);

        // Employee without finance permission is fenced from money PDFs.
        $eng = User::factory()->create(['role' => 'employee', 'is_active' => true]);
        $this->actingAs($eng)->get(route('admin.receipts.pdf', $ra['receipt']))->assertStatus(403);
    }

    /** @test */
    public function failed_and_partial_payments_behave_correctly()
    {
        $finance = User::factory()->create(['role' => 'finance_manager', 'is_active' => true]);
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $cat = ServiceCategory::create(['name' => 'Audit Cat', 'slug' => 'audit-cat-fail']);
        $service = Service::create(['category_id' => $cat->id, 'name' => 'Fail Service', 'slug' => 'fail-service']);
        $order = ServiceOrder::create(['order_number' => 'ORD-FAIL-1', 'customer_id' => $customer->id, 'service_id' => $service->id, 'requirements' => 'Req', 'status' => 'confirmed', 'total' => 1000, 'amount_paid' => 0, 'amount_due' => 1000]);
        Invoice::create(['invoice_number' => 'INV-FAIL-1', 'customer_id' => $customer->id, 'service_order_id' => $order->id, 'subtotal' => 1000, 'total' => 1000, 'amount_paid' => 0, 'amount_due' => 1000, 'status' => 'sent', 'due_date' => now()->addDays(14)]);

        // Zero/negative amounts rejected: no payment, no receipt, no credit.
        $this->actingAs($finance)->post(route('admin.work-orders.record-payment', $order), ['amount' => 0, 'payment_method' => 'cash'])->assertSessionHasErrors();
        $this->assertSame(0, Payment::where('service_order_id', $order->id)->count());
        $this->assertSame(1000.0, (float) $order->fresh()->amount_due);

        // Partial then remainder reconciles exactly.
        $this->actingAs($finance)->post(route('admin.work-orders.record-payment', $order), ['amount' => 350, 'payment_method' => 'bank_transfer'])->assertSessionHas('success');
        $this->assertSame('partially_paid', $order->fresh()->invoices()->first()->status);
        $this->actingAs($finance)->post(route('admin.work-orders.record-payment', $order), ['amount' => 650, 'payment_method' => 'bank_transfer'])->assertSessionHas('success');
        $order->refresh();
        $this->assertSame(1000.0, (float) $order->amount_paid);
        $this->assertSame(0.0, (float) $order->amount_due);
    }
}
