<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\FinancialTransaction;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\FinancialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialTest extends TestCase
{
    use RefreshDatabase;

    protected $financeUser;
    protected $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->financeUser = User::factory()->create(['role' => 'finance_manager']);
        $this->customer = User::factory()->create(['role' => 'customer']);
    }

    public function test_invoice_can_be_created()
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-TEST001',
            'customer_id' => $this->customer->id,
            'subtotal' => 100,
            'total' => 110,
            'tax_rate' => 10,
            'tax_amount' => 10,
            'amount_due' => 110,
            'status' => 'draft',
            'due_date' => now()->addDays(30),
        ]);

        $this->assertDatabaseHas('invoices', ['invoice_number' => 'INV-TEST001', 'status' => 'draft']);
    }

    public function test_invoice_recalculates_totals()
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-CALC01',
            'customer_id' => $this->customer->id,
            'tax_rate' => 10,
            'status' => 'draft',
            'due_date' => now()->addDays(30),
        ]);

        InvoiceItem::create(['invoice_id' => $invoice->id, 'description' => 'Item 1', 'quantity' => 2, 'unit_price' => 100, 'total' => 200]);
        InvoiceItem::create(['invoice_id' => $invoice->id, 'description' => 'Item 2', 'quantity' => 1, 'unit_price' => 50, 'total' => 50]);

        $invoice->recalculate();

        $this->assertEquals(250, $invoice->subtotal);
        $this->assertEquals(25, $invoice->tax_amount);
        $this->assertEquals(275, $invoice->total);
    }

    public function test_payment_updates_invoice_status()
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-PAY001',
            'customer_id' => $this->customer->id,
            'total' => 100,
            'amount_due' => 100,
            'status' => 'sent',
            'due_date' => now()->addDays(30),
        ]);

        $payment = Payment::create([
            'payment_number' => 'PAY-TEST001',
            'invoice_id' => $invoice->id,
            'customer_id' => $this->customer->id,
            'amount' => 100,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        $invoice->amount_paid += $payment->amount;
        $invoice->amount_due = $invoice->total - $invoice->amount_paid;
        $invoice->status = $invoice->amount_due <= 0 ? 'paid' : 'partially_paid';
        $invoice->save();

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals(0, $invoice->amount_due);
    }

    public function test_financial_transaction_records_income()
    {
        $txn = FinancialTransaction::create([
            'type' => 'income',
            'category' => 'Customer Payment',
            'description' => 'Test payment',
            'amount' => 500,
            'status' => 'completed',
            'created_by' => $this->financeUser->id,
        ]);

        $this->assertDatabaseHas('financial_transactions', ['type' => 'income', 'amount' => 500]);
    }

    public function test_financial_service_calculates_profit_and_loss()
    {
        // Create income
        FinancialTransaction::create(['type' => 'income', 'category' => 'Sales', 'description' => 'Sale', 'amount' => 10000, 'status' => 'completed', 'created_by' => $this->financeUser->id]);

        // Create expenses
        FinancialTransaction::create(['type' => 'expense', 'category' => 'Hosting', 'description' => 'Hosting', 'amount' => 500, 'status' => 'completed', 'created_by' => $this->financeUser->id]);

        $fs = app(FinancialService::class);
        $pnl = $fs->getProfitAndLoss(now()->startOfMonth(), now()->endOfMonth());

        $this->assertEquals(10000, $pnl['revenue']);
        $this->assertEquals(500, $pnl['total_expenses']);
        $this->assertEquals(9500, $pnl['net_profit']);
    }

    public function test_expense_category_exists()
    {
        ExpenseCategory::create(['name' => 'Software', 'slug' => 'software']);
        $this->assertDatabaseHas('expense_categories', ['slug' => 'software']);
    }
}
