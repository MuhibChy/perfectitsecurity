<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Invoice;
use App\Models\ManualBankPayment;
use App\Models\Payment;
use App\Models\PaymentProvider;
use App\Models\PaymentRefund;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Services\PaymentCheckoutService;
use App\Services\PaymentProviderService;
use App\Services\PaymentRefundService;
use App\Services\PaymentSettlementService;
use App\Services\PaymentWebhookService;
use App\Services\ProviderReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Flexible multi-provider payment layer: registry, checkout, webhooks,
 * bank-transfer verification, refunds, FX, reconciliation — all additive.
 * Existing rails (Stripe/manual/wallet) are untouched and covered by
 * their own suites; this file proves the new layer + its ledger sync.
 */
class PaymentProviderSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function customer(array $over = []): User
    {
        return User::factory()->create(array_merge(['role' => 'customer', 'is_active' => true, 'preferred_currency' => 'USD'], $over));
    }

    protected function staff(string $role): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true]);
    }

    protected function invoice(User $customer, float $total = 1000, array $over = []): Invoice
    {
        return Invoice::create(array_merge([
            'invoice_number' => 'INV-PT-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'customer_id' => $customer->id, 'subtotal' => $total, 'total' => $total,
            'amount_paid' => 0, 'amount_due' => $total, 'status' => 'sent',
            'currency' => 'USD', 'due_date' => now()->addDays(14),
        ], $over));
    }

    protected function provider(string $key = 'bkash', array $over = []): PaymentProvider
    {
        return PaymentProvider::create(array_merge([
            'key' => $key, 'name' => ucfirst($key), 'type' => 'mobile_wallet',
            'country' => 'Bangladesh', 'currencies' => ['BDT'],
            'environment' => 'test', 'status' => 'enabled', 'priority' => 10,
            'is_active' => true,
        ], $over));
    }

    /** @test */
    public function provider_secrets_are_encrypted_at_rest_and_never_shown()
    {
        $p = $this->provider('bkash');
        $p->credentials = ['api_key' => 'super-secret-key', 'secret_key' => 'shh'];
        $p->webhook_secret = 'whsec-123';
        $p->save();

        $raw = DB::table('payment_providers')->where('id', $p->id)->first();
        $this->assertStringNotContainsString('super-secret-key', (string) $raw->credentials);
        $this->assertStringNotContainsString('whsec-123', (string) $raw->webhook_secret);
        $this->assertSame('super-secret-key', $p->fresh()->credential('api_key'));
        $this->assertSame('whsec-123', $p->fresh()->webhook_secret);

        $this->actingAs($this->staff('finance_manager'))
            ->get(route('admin.payment-providers.index'))
            ->assertStatus(200)
            ->assertDontSee('super-secret-key', false)
            ->assertDontSee('whsec-123', false);
    }

    /** @test */
    public function bank_account_numbers_are_encrypted_and_masked()
    {
        $finance = $this->staff('finance_manager');
        $this->actingAs($finance)->post(route('admin.bank-accounts.store'), [
            'label' => 'EBL Collections', 'country' => 'Bangladesh', 'currency' => 'BDT',
            'account_name' => 'PerfectITSecurity', 'bank_name' => 'Eastern Bank',
            'purpose' => 'collections',
        ])->assertRedirect();
        $account = BankAccount::firstOrFail();
        $account->account_number = '1234567890123456';
        $account->save();

        $raw = DB::table('bank_accounts')->where('id', $account->id)->first();
        $this->assertStringNotContainsString('1234567890', (string) $raw->account_number);
        $this->assertSame('****3456', $account->fresh()->maskedNumber());
        $this->actingAs($finance)->get(route('admin.bank-accounts.index'))
            ->assertStatus(200)->assertDontSee('1234567890', false);
    }

    /** @test */
    public function checkout_initiate_is_idempotent_and_server_priced()
    {
        $customer = $this->customer();
        $invoice = $this->invoice($customer, 1000);
        $this->provider('manual', ['currencies' => null, 'country' => 'International']);
        $svc = app(PaymentCheckoutService::class);

        $first = $svc->initiate($invoice, $customer->id, 'manual', ['amount' => 99999, 'idempotency_key' => 'chk_dup_1']);
        $this->assertFalse($first['duplicate']);
        // Browser asked for 99999 — server clamped to the 1000 outstanding.
        $this->assertSame(1000.0, (float) $first['transaction']->original_amount);

        $second = $svc->initiate($invoice, $customer->id, 'manual', ['amount' => 1000, 'idempotency_key' => 'chk_dup_1']);
        $this->assertTrue($second['duplicate']);
        $this->assertSame($first['transaction']->id, $second['transaction']->id);
        $this->assertSame(1, PaymentTransaction::where('invoice_id', $invoice->id)->count());
    }

    /** @test */
    public function checkout_rejects_foreign_invoices_and_unpayable_status()
    {
        $a = $this->customer();
        $b = $this->customer();
        $invoice = $this->invoice($b, 500);
        $this->provider('manual', ['currencies' => null, 'country' => 'International']);
        $svc = app(PaymentCheckoutService::class);

        try {
            $svc->initiate($invoice, $a->id, 'manual', []);
            $this->fail('Expected 403 for foreign invoice.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        $invoice->update(['status' => 'draft']);
        try {
            $svc->initiate($invoice, $b->id, 'manual', []);
            $this->fail('Expected 422 for draft invoice.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());
        }
    }

    /** @test */
    public function provider_availability_filters_currency_amount_and_orders_priority()
    {
        $this->provider('bkash', ['priority' => 10]);
        $this->provider('nagad', ['priority' => 5]);
        $this->provider('rocket', ['priority' => 50, 'status' => 'disabled']);
        $this->provider('bank_transfer', ['type' => 'bank', 'country' => 'Bangladesh/International', 'currencies' => null, 'priority' => 30]);
        $svc = app(PaymentProviderService::class);

        $keys = array_map(fn ($p) => $p->key, $svc->availableFor('BDT', 500, 'Bangladesh'));
        $this->assertSame(['nagad', 'bkash', 'bank_transfer'], $keys);

        // USD filters out the BDT-only wallets.
        $usd = array_map(fn ($p) => $p->key, $svc->availableFor('USD', 500, 'United Kingdom'));
        $this->assertNotContains('bkash', $usd);
        $this->assertContains('bank_transfer', $usd);

        // Amount limits respected.
        PaymentProvider::where('key', 'nagad')->update(['min_amount' => 1000]);
        $keys = array_map(fn ($p) => $p->key, $svc->availableFor('BDT', 500, 'Bangladesh'));
        $this->assertNotContains('nagad', $keys);
    }

    /** @test */
    public function webhook_settles_exactly_once_and_posts_ledger()
    {
        $customer = $this->customer();
        $invoice = $this->invoice($customer, 1000);
        $this->provider('bkash');
        $txn = PaymentTransaction::create([
            'customer_id' => $customer->id, 'invoice_id' => $invoice->id,
            'provider_key' => 'bkash', 'payment_method' => 'bkash',
            'provider_reference' => 'BKASH-TEST-1',
            'original_amount' => 400, 'original_currency' => 'USD',
            'settlement_currency' => 'USD', 'provider_currency' => 'USD', 'provider_amount' => 400,
            'gross_amount' => 400, 'net_amount' => 400, 'status' => 'pending',
        ]);
        $webhooks = app(PaymentWebhookService::class);

        $first = $webhooks->ingest('bkash', 'evt-1', ['transaction_reference' => $txn->reference, 'provider_status' => 'paid'], null, '{}');
        $this->assertTrue($first['settled']);
        // 400 of 1000 outstanding → partially paid, balance math exact.
        $this->assertSame('partially_paid', $invoice->fresh()->status);
        $this->assertSame(400.0, (float) $invoice->fresh()->amount_paid);
        $this->assertSame(600.0, (float) $invoice->fresh()->amount_due);
        $this->assertDatabaseHas('payments', ['transaction_id' => $txn->reference, 'status' => 'completed']);
        $this->assertDatabaseHas('receipts', ['invoice_id' => $invoice->id, 'amount' => 400]);
        $this->assertDatabaseHas('financial_transactions', ['payment_id' => $txn->fresh()->payment_id, 'type' => 'income']);

        // Replay of the same event: duplicate, no second payment.
        $replay = $webhooks->ingest('bkash', 'evt-1', ['transaction_reference' => $txn->reference, 'provider_status' => 'paid'], null, '{}');
        $this->assertTrue($replay['duplicate']);
        $this->assertSame(1, Payment::where('transaction_id', $txn->reference)->count());
        $this->assertSame(400.0, (float) $invoice->fresh()->amount_paid);
    }

    /** @test */
    public function webhook_with_unknown_reference_moves_no_money()
    {
        $this->provider('nagad');
        $result = app(PaymentWebhookService::class)->ingest('nagad', 'evt-ghost', ['transaction_reference' => 'PTXN-NOPE', 'provider_status' => 'paid'], null, '{}');
        $this->assertFalse($result['settled'] ?? true);
        $this->assertSame(0, Payment::count());
        $this->assertSame(0, \App\Models\FinancialTransaction::count());
    }

    /** @test */
    public function bank_transfer_submit_never_autopays_and_verify_settles_once()
    {
        $customer = $this->customer();
        $invoice = $this->invoice($customer, 800);
        $this->provider('bank_transfer', ['type' => 'bank', 'country' => 'Bangladesh/International', 'currencies' => null, 'priority' => 30]);
        $account = BankAccount::create(['label' => 'EBL', 'country' => 'Bangladesh', 'currency' => 'USD', 'account_name' => 'PITS', 'bank_name' => 'EBL', 'purpose' => 'collections', 'is_active' => true]);
        $txn = PaymentTransaction::create([
            'customer_id' => $customer->id, 'invoice_id' => $invoice->id,
            'provider_key' => 'bank_transfer', 'payment_method' => 'bank_transfer',
            'original_amount' => 800, 'original_currency' => 'USD', 'settlement_currency' => 'USD',
            'gross_amount' => 800, 'net_amount' => 800, 'status' => 'pending_verification',
        ]);

        $this->actingAs($customer)->post(route('portal.bank-transfer.submit', $txn->reference), [
            'bank_account_id' => $account->id, 'amount' => 800, 'sender_name' => 'Alice',
            'sender_bank' => 'DBBL', 'transfer_reference' => 'FT-1',
        ])->assertRedirect();
        $this->assertSame('sent', $invoice->fresh()->status); // still unpaid
        $this->assertSame(0, Payment::count());
        $mbp = ManualBankPayment::firstOrFail();
        $this->assertSame('pending_verification', $mbp->status);

        $finance = $this->staff('finance_manager');
        $this->actingAs($finance)->post(route('admin.bank-transfers.verify', $mbp), ['admin_notes' => 'Seen in statement.'])->assertRedirect();
        $this->assertSame('verified', $mbp->fresh()->status);
        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame(1, Payment::where('transaction_id', $txn->reference)->count());

        // Second verify attempt is rejected — no double ledger.
        $this->actingAs($finance)->post(route('admin.bank-transfers.verify', $mbp))->assertStatus(422);
        $this->assertSame(1, Payment::where('transaction_id', $txn->reference)->count());
        $this->assertSame(1, \App\Models\FinancialTransaction::where('type', 'income')->count());
    }

    /** @test */
    public function refund_workflow_rolls_back_once_and_needs_approval()
    {
        $customer = $this->customer();
        $invoice = $this->invoice($customer, 1000);
        $payment = Payment::create([
            'invoice_id' => $invoice->id, 'customer_id' => $customer->id,
            'amount' => 1000, 'currency' => 'USD', 'payment_method' => 'bkash',
            'gateway' => 'bkash', 'transaction_id' => 'PTXN-RF-1', 'status' => 'completed', 'paid_at' => now(),
        ]);
        $invoice->update(['amount_paid' => 1000, 'amount_due' => 0, 'status' => 'paid']);
        $svc = app(PaymentRefundService::class);

        $refund = $svc->request($payment, 400, 'USD', 'Customer charged for unrendered module', $customer->id);
        $this->assertSame('requested', $refund->status);
        // Execute before approval is refused.
        try {
            $svc->execute($refund, $this->staff('finance_manager')->id);
            $this->fail('Expected 422 for unapproved execution.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());
        }
        $svc->approve($refund, $this->staff('finance_manager')->id);
        $done = $svc->execute($refund->fresh(), $this->staff('finance_manager')->id);
        $this->assertSame('refunded', $done->status);
        $this->assertSame(400.0, (float) $payment->fresh()->refunded_amount);
        $this->assertSame('partially_paid', $invoice->fresh()->status);
        $this->assertSame(600.0, (float) $invoice->fresh()->amount_paid);
        $this->assertDatabaseHas('financial_transactions', ['type' => 'refund', 'amount' => 400]);
        // Second execution refused.
        try {
            $svc->execute($done, $this->staff('finance_manager')->id);
            $this->fail('Expected 422 for double execution.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());
        }
    }

    /** @test */
    public function fx_conversion_is_recorded_and_history_immutable()
    {
        $customer = $this->customer(['country' => 'Bangladesh']);
        $invoice = $this->invoice($customer, 500, ['currency' => 'GBP']);
        $this->provider('bkash', ['priority' => 10]);
        // Seed cross-via-USD rates (fail-closed service needs rows first).
        \App\Models\ExchangeRate::create(['base_currency' => 'USD', 'target_currency' => 'GBP', 'rate' => 0.79, 'fetched_at' => now()]);
        \App\Models\ExchangeRate::create(['base_currency' => 'USD', 'target_currency' => 'BDT', 'rate' => 110, 'fetched_at' => now()]);
        $result = app(PaymentCheckoutService::class)->initiate($invoice, $customer->id, 'bkash', ['pay_currency' => 'BDT']);
        $txn = $result['transaction'];
        $this->assertSame('GBP', $txn->original_currency);
        $this->assertSame('BDT', $txn->provider_currency);
        $this->assertNotNull($txn->exchange_rate);
        $this->assertGreaterThan(500, (float) $txn->provider_amount);
        // Rate snapshot survives a later rate change.
        \App\Models\ExchangeRate::updateOrCreate(['base_currency' => 'GBP', 'target_currency' => 'BDT'], ['rate' => 1.0, 'fetched_at' => now()]);
        $this->assertNotEquals(1.0, (float) $txn->fresh()->exchange_rate);
    }

    /** @test */
    public function illegal_status_transitions_are_rejected()
    {
        $txn = PaymentTransaction::create([
            'customer_id' => $this->customer()->id,
            'provider_key' => 'manual', 'original_amount' => 10,
            'original_currency' => 'USD', 'gross_amount' => 10, 'net_amount' => 10,
            'status' => 'paid',
        ]);
        $this->assertFalse($txn->transitionTo('pending'));
        $this->assertSame('paid', $txn->fresh()->status);
        $this->assertTrue($txn->isTerminal());
    }

    /** @test */
    public function reconciliation_sweep_flags_matched_and_review()
    {
        $customer = $this->customer();
        $invoice = $this->invoice($customer, 200);
        $this->provider('bkash');
        $txn = PaymentTransaction::create([
            'customer_id' => $customer->id, 'invoice_id' => $invoice->id,
            'provider_key' => 'bkash', 'payment_method' => 'bkash',
            'provider_reference' => 'BKASH-R-1',
            'original_amount' => 200, 'original_currency' => 'USD',
            'settlement_currency' => 'USD', 'provider_currency' => 'USD', 'provider_amount' => 200,
            'gross_amount' => 200, 'net_amount' => 200, 'status' => 'pending',
        ]);
        app(PaymentWebhookService::class)->ingest('bkash', 'evt-r1', ['transaction_reference' => $txn->reference, 'provider_status' => 'paid'], null, '{}');
        $summary = app(ProviderReconciliationService::class)->sweep(50);
        $this->assertSame(1, $summary['checked']);
        $this->assertSame(1, $summary['matched']);
        $this->assertDatabaseHas('payment_reconciliation_records', ['internal_reference' => $txn->reference, 'result' => 'matched']);
    }

    /** @test */
    public function payment_admin_routes_are_finance_gated()
    {
        // Guests are bounced to login before any finance check runs.
        $this->get(route('admin.payments.overview'))->assertStatus(302);

        $agent = $this->staff('support_agent');
        $customer = $this->customer();
        $finance = $this->staff('finance_manager');

        $this->actingAs($agent)->get(route('admin.payments.overview'))->assertStatus(403);
        $this->actingAs($agent)->get(route('admin.payment-providers.index'))->assertStatus(403);
        $this->actingAs($agent)->get(route('admin.bank-accounts.index'))->assertStatus(403);
        $this->actingAs($agent)->get(route('admin.bank-transfers.index'))->assertStatus(403);
        $this->actingAs($agent)->get(route('admin.refunds.index'))->assertStatus(403);
        $this->actingAs($customer)->get(route('admin.payments.overview'))->assertStatus(403);

        $this->actingAs($finance)->get(route('admin.payments.overview'))->assertStatus(200);
        $this->actingAs($finance)->get(route('admin.payment-providers.index'))->assertStatus(200);
        $this->actingAs($finance)->get(route('admin.bank-accounts.index'))->assertStatus(200);
    }

    /** @test */
    public function customer_checkout_and_history_are_owner_scoped()
    {
        // Guest first (actingAs persists per test, so unauthenticated goes first).
        $this->get(route('portal.payments.index'))->assertStatus(302);

        $a = $this->customer();
        $b = $this->customer();
        $invoice = $this->invoice($b, 300);
        $this->provider('manual', ['currencies' => null, 'country' => 'International']);

        $this->actingAs($a)->get(route('portal.checkout.show', $invoice->id))->assertStatus(404);
        $this->actingAs($b)->get(route('portal.checkout.show', $invoice->id))->assertStatus(200);

        // Isolation probe: guessing another customer's payment URL finds nothing.
        $txn = PaymentTransaction::create([
            'customer_id' => $b->id, 'invoice_id' => $invoice->id,
            'provider_key' => 'manual', 'original_amount' => 300,
            'original_currency' => 'USD', 'gross_amount' => 300, 'net_amount' => 300,
            'status' => 'pending',
        ]);
        $this->actingAs($a)->get(route('portal.payments.show', $txn->reference))->assertStatus(404);
        $this->actingAs($b)->get(route('portal.payments.show', $txn->reference))->assertStatus(200);
    }

    /** @test */
    public function payment_events_notify_customer_and_finance_staff()
    {
        $customer = $this->customer();
        $finance = $this->staff('finance_manager');
        $invoice = $this->invoice($customer, 500);
        $this->provider('bkash');
        $webhooks = app(PaymentWebhookService::class);

        $txn = PaymentTransaction::create([
            'customer_id' => $customer->id, 'invoice_id' => $invoice->id,
            'provider_key' => 'bkash', 'payment_method' => 'bkash',
            'provider_reference' => 'BKASH-NOTIFY-1',
            'original_amount' => 500, 'original_currency' => 'USD',
            'settlement_currency' => 'USD', 'provider_currency' => 'USD', 'provider_amount' => 500,
            'gross_amount' => 500, 'net_amount' => 500, 'status' => 'pending',
        ]);
        $webhooks->ingest('bkash', 'evt-n1', ['transaction_reference' => $txn->reference, 'provider_status' => 'paid'], null, '{}');
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => \App\Models\User::class,
            'notifiable_id' => $customer->id,
            'type' => \App\Notifications\PaymentStatusNotification::class,
            'data->event' => 'payment_successful',
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $finance->id,
            'data->event' => 'payment_received',
        ]);

        $failed = PaymentTransaction::create([
            'customer_id' => $customer->id, 'invoice_id' => $invoice->id,
            'provider_key' => 'bkash', 'payment_method' => 'bkash',
            'provider_reference' => 'BKASH-NOTIFY-2',
            'original_amount' => 100, 'original_currency' => 'USD',
            'settlement_currency' => 'USD', 'provider_currency' => 'USD', 'provider_amount' => 100,
            'gross_amount' => 100, 'net_amount' => 100, 'status' => 'pending',
        ]);
        $webhooks->ingest('bkash', 'evt-n2', ['transaction_reference' => $failed->reference, 'provider_status' => 'failed'], null, '{}');
        $this->assertSame('failed', $failed->fresh()->status);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $customer->id,
            'data->event' => 'payment_failed',
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $finance->id,
            'data->event' => 'payment_failed',
        ]);
    }

    /** @test */
    public function refund_requests_ping_finance_staff()
    {
        $customer = $this->customer();
        $finance = $this->staff('finance_manager');
        $invoice = $this->invoice($customer, 1000);
        $payment = Payment::create([
            'invoice_id' => $invoice->id, 'customer_id' => $customer->id,
            'amount' => 1000, 'currency' => 'USD', 'payment_method' => 'manual',
            'gateway' => 'manual', 'transaction_id' => 'PTXN-RFN-1', 'status' => 'completed', 'paid_at' => now(),
        ]);
        $invoice->update(['amount_paid' => 1000, 'amount_due' => 0, 'status' => 'paid']);

        app(PaymentRefundService::class)->request($payment, 100, 'USD', 'Charged twice by accident, sorry', $customer->id);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $finance->id,
            'type' => \App\Notifications\PaymentStatusNotification::class,
            'data->event' => 'refund_requested',
        ]);
    }

    /** @test */
    public function partial_payments_accumulate_without_new_invoices()
    {
        $customer = $this->customer();
        $invoice = $this->invoice($customer, 1000);
        $this->provider('bkash');
        $webhooks = app(PaymentWebhookService::class);
        foreach ([['t1', 'e1', 300], ['t2', 'e2', 400], ['t3', 'e3', 300]] as [$ref, $evt, $amount]) {
            $txn = PaymentTransaction::create([
                'customer_id' => $customer->id, 'invoice_id' => $invoice->id,
                'provider_key' => 'bkash', 'payment_method' => 'bkash',
                'provider_reference' => 'BKASH-' . $ref,
                'original_amount' => $amount, 'original_currency' => 'USD',
                'settlement_currency' => 'USD', 'gross_amount' => $amount, 'net_amount' => $amount,
                'status' => 'pending',
            ]);
            $webhooks->ingest('bkash', $evt, ['transaction_reference' => $txn->reference, 'provider_status' => 'paid'], null, '{}');
        }
        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame(1000.0, (float) $invoice->amount_paid);
        $this->assertSame(1, Invoice::where('customer_id', $customer->id)->count());
        $this->assertSame(3, Payment::where('invoice_id', $invoice->id)->where('status', 'completed')->count());
    }
}
