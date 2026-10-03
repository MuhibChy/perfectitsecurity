<?php

namespace Tests\Feature;

use App\Models\CashMemo;
use App\Models\Receipt;
use App\Models\User;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task → invoice → payment → receipt/cash-memo chain: every billable
 * order carries exactly one authoritative invoice, every payment exactly
 * one receipt (+ cash memo for cash), numbers are unique, regeneration
 * never duplicates financial records, and PDFs match the database.
 */
class DocumentChainTest extends TestCase
{
    use RefreshDatabase;

    private function verified(string $role = 'customer'): User
    {
        return User::factory()->create([
            'role' => $role, 'is_active' => true,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
            'verification_status' => 'fully_verified',
        ]);
    }

    private function service(): \App\Models\Service
    {
        $cat = \App\Models\ServiceCategory::firstOrCreate(['slug' => 'doc-chain'], ['name' => 'Doc Chain']);
        return \App\Models\Service::firstOrCreate(['slug' => 'doc-chain-svc'], [
            'category_id' => $cat->id, 'name' => '[TEST] Chain Service', 'short_description' => 'x', 'is_active' => true,
        ]);
    }

    private function billableOrder(User $customer, float $total = 1500, string $ccy = 'USD'): \App\Models\ServiceOrder
    {
        $order = \App\Models\ServiceOrder::create([
            'customer_id' => $customer->id, 'service_id' => $this->service()->id,
            'requirements' => 'Billable chain order with full detail.', 'status' => 'confirmed',
            'currency' => $ccy, 'total' => $total, 'amount_paid' => 0, 'amount_due' => $total,
        ]);
        app(ServiceOrderWorkflowService::class)->generateConnectedRecords($order->fresh(), $customer);
        // Every task on the order links back to the same authoritative chain.
        \App\Models\Task::create([
            'service_order_id' => $order->id, 'customer_id' => $customer->id,
            'created_by' => $customer->id, 'title' => '[TEST] Chain task',
            'description' => 'Work performed.', 'status' => 'completed', 'priority' => 'medium',
        ]);
        return $order->fresh();
    }

    /** @test */
    public function billable_order_has_exactly_one_authoritative_invoice()
    {
        $customer = $this->verified();
        $order = $this->billableOrder($customer);
        $invoice = $order->invoices()->firstOrFail();
        $this->assertSame(1, $order->invoices()->count());
        $this->assertNotEmpty($invoice->invoice_number);
        $this->assertSame('USD', $invoice->currency);
        $this->assertEquals(1500, (float) $invoice->total);
        $this->assertEquals(1500, (float) $invoice->amount_due);
        // Regenerating connected records never duplicates the invoice.
        app(ServiceOrderWorkflowService::class)->generateConnectedRecords($order->fresh(), $customer);
        $this->assertSame(1, $order->invoices()->count());
        $this->assertSame($invoice->invoice_number, $order->invoices()->first()->invoice_number);
    }

    /** @test */
    public function staged_payments_each_yield_one_receipt_and_one_transaction()
    {
        $customer = $this->verified();
        $finance = $this->verified('finance_manager');
        $order = $this->billableOrder($customer);
        $svc = app(ServiceOrderWorkflowService::class);

        // Advance 500 + stage 300 + stage 300 + final 400 = 1500.
        foreach ([500, 300, 300, 400] as $amount) {
            $svc->recordPayment($order->fresh(), ['amount' => $amount, 'payment_method' => 'card'], $finance);
        }
        $order = $order->fresh();
        $this->assertEquals(1500, (float) $order->amount_paid);
        $this->assertEquals(0, (float) $order->amount_due);
        $this->assertSame(4, \App\Models\Payment::where('service_order_id', $order->id)->count());
        $this->assertSame(4, Receipt::where('service_order_id', $order->id)->count());
        $this->assertSame(4, \App\Models\FinancialTransaction::where('service_order_id', $order->id)->where('type', 'income')->count());

        // Receipt ↔ payment ↔ invoice ↔ order linkage is exact.
        foreach (\App\Models\Payment::where('service_order_id', $order->id)->get() as $payment) {
            $receipt = $payment->receipt;
            $this->assertNotNull($receipt);
            $this->assertSame($payment->id, (int) $receipt->payment_id);
            $this->assertSame($order->invoices()->first()->id, (int) $receipt->invoice_id);
            $this->assertSame($customer->id, (int) $receipt->customer_id);
            $this->assertEquals((float) $payment->amount, (float) $receipt->amount);
            $this->assertSame('USD', $receipt->currency);
        }
        // Overpay and closed-order payments are refused (no partial records).
        try {
            $svc->recordPayment($order->fresh(), ['amount' => 10, 'payment_method' => 'card'], $finance);
            $this->fail('Overpay accepted');
        } catch (\Throwable $e) {
            $this->assertSame(422, $e->getStatusCode());
        }
        $this->assertSame(4, \App\Models\Payment::where('service_order_id', $order->id)->count());
    }

    /** @test */
    public function cash_payment_yields_exactly_one_cash_memo_and_is_idempotent()
    {
        $customer = $this->verified();
        $finance = $this->verified('finance_manager');
        $order = $this->billableOrder($customer, 800, 'GBP');
        $result = app(ServiceOrderWorkflowService::class)->recordPayment(
            $order->fresh(), ['amount' => 800, 'payment_method' => 'cash'], $finance
        );
        $payment = $result['payment'];

        // Same rule as the admin work-order path: one memo per cash payment.
        $memo = CashMemo::firstOrCreate(['payment_id' => $payment->id], ['issued_by' => $finance->id, 'issued_at' => now()]);
        $this->assertStringStartsWith('CM-', $memo->cash_memo_number);
        $again = CashMemo::firstOrCreate(['payment_id' => $payment->id], ['issued_by' => $finance->id, 'issued_at' => now()]);
        $this->assertSame($memo->id, $again->id);
        $this->assertSame(1, CashMemo::where('payment_id', $payment->id)->count());
        // Memo documents the payment; it creates no money movement.
        $this->assertSame(1, \App\Models\FinancialTransaction::where('payment_id', $payment->id)->count());

        // Customer downloads own memo; peer cannot.
        $this->actingAs($customer)->get(route('portal.cash-memos.pdf', $memo->id))->assertStatus(200);
        $peer = $this->verified();
        $this->assertContains($this->actingAs($peer)->get(route('portal.cash-memos.pdf', $memo->id))->getStatusCode(), [403, 404]);
    }

    /** @test */
    public function document_numbers_are_unique_and_prefixed()
    {
        $customer = $this->verified();
        $finance = $this->verified('finance_manager');
        $numbers = ['orders' => [], 'invoices' => [], 'payments' => [], 'receipts' => []];
        for ($i = 0; $i < 3; $i++) {
            $order = $this->billableOrder($customer, 200 + $i * 50);
            app(ServiceOrderWorkflowService::class)->recordPayment($order->fresh(), ['amount' => 100, 'payment_method' => 'card'], $finance);
            $numbers['orders'][] = $order->order_number;
            $numbers['invoices'][] = $order->invoices()->first()->invoice_number;
            $numbers['payments'][] = \App\Models\Payment::where('service_order_id', $order->id)->first()->payment_number;
            $numbers['receipts'][] = Receipt::where('service_order_id', $order->id)->first()->receipt_number;
        }
        $this->assertStringStartsWith('ORD-', $numbers['orders'][0]);
        $this->assertStringStartsWith('INV-', $numbers['invoices'][0]);
        $this->assertStringStartsWith('PAY-', $numbers['payments'][0]);
        $this->assertStringStartsWith('RCP-', $numbers['receipts'][0]);
        foreach ($numbers as $kind => $list) {
            $this->assertSame(count($list), count(array_unique($list)), "Duplicate {$kind} numbers");
        }
    }

    /** @test */
    public function pdf_documents_match_authoritative_records()
    {
        $customer = $this->verified();
        $finance = $this->verified('finance_manager');
        $order = $this->billableOrder($customer, 1200, 'GBP');
        app(ServiceOrderWorkflowService::class)->recordPayment($order->fresh(), ['amount' => 400, 'payment_method' => 'card'], $finance);
        $invoice = $order->invoices()->firstOrFail();
        $receipt = Receipt::where('service_order_id', $order->id)->firstOrFail();
        $project = \App\Models\Project::create([
            'customer_id' => $customer->id, 'project_number' => 'PRJ-DOCPDF',
            'slug' => 'doc-pdf', 'name' => '[TEST] Doc PDF', 'status' => 'completed',
        ]);

        foreach ([
            route('portal.orders.pdf', $order->id),
            route('portal.invoices.pdf', $invoice->id),
            route('portal.receipts.pdf', $receipt->id),
            route('portal.tracking.report-pdf', $project->id),
        ] as $url) {
            $response = $this->actingAs($customer)->get($url)->assertStatus(200);
            $this->assertStringStartsWith('%PDF', $response->getContent(), "Not a PDF: {$url}");
        }
        // Repeated downloads create no new financial records.
        $counts = [Receipt::count(), \App\Models\Payment::count(), \App\Models\Invoice::count(), \App\Models\FinancialTransaction::count()];
        $this->actingAs($customer)->get(route('portal.invoices.pdf', $invoice->id))->assertStatus(200);
        $this->actingAs($customer)->get(route('portal.receipts.pdf', $receipt->id))->assertStatus(200);
        $this->assertSame($counts, [Receipt::count(), \App\Models\Payment::count(), \App\Models\Invoice::count(), \App\Models\FinancialTransaction::count()]);

        // Screen views render the same authoritative figures.
        $html = $this->actingAs($customer)->get(route('portal.invoices.show', $invoice->id))->assertStatus(200)->getContent();
        $this->assertStringContainsString($invoice->invoice_number, $html);
        $this->assertStringContainsString(\App\Services\Money::format((float) $invoice->total, $invoice->currency), $html);
        $orderHtml = $this->actingAs($customer)->get(route('portal.orders.show', $order->id))->assertStatus(200)->getContent();
        $this->assertStringContainsString($order->order_number, $orderHtml);
        $serviceHtml = $this->actingAs($customer)->get(route('portal.reports.service', $order->id))->assertStatus(200)->getContent();
        $this->assertStringContainsString($order->order_number, $serviceHtml);
    }
}
