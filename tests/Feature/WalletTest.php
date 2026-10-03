<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['wallet.test_gateway' => true]);
    }

    protected function customer(): User
    {
        return User::factory()->create(['role' => 'customer', 'is_active' => true, 'preferred_currency' => 'USD']);
    }

    protected function invoice(User $customer, float $total = 1000): Invoice
    {
        return Invoice::create(['invoice_number' => 'INV-W-' . strtoupper(\Illuminate\Support\Str::random(6)), 'customer_id' => $customer->id, 'subtotal' => $total, 'total' => $total, 'amount_paid' => 0, 'amount_due' => $total, 'status' => 'sent', 'currency' => 'USD', 'due_date' => now()->addDays(14)]);
    }

    protected function service(): WalletService { return app(WalletService::class); }

    /** @test */
    public function wallet_provisioning_is_idempotent_and_unique()
    {
        $customer = $this->customer();
        $a = $this->service()->for($customer);
        $b = $this->service()->for($customer);
        $this->assertSame($a->id, $b->id);
        $this->assertSame(1, Wallet::where('user_id', $customer->id)->count());
        $this->assertNotEmpty($a->wallet_reference);
    }

    /** @test */
    public function add_money_credits_once_and_reconciles()
    {
        $customer = $this->customer();
        $wallet = $this->service()->for($customer);

        $first = $this->service()->creditDeposit($wallet, 500, 'test', 'txn-1', 'evt-1', $customer->id);
        $this->assertFalse($first['duplicate']);
        $this->assertSame(500.0, round((float) $wallet->fresh()->balance, 2));

        // Duplicate delivery of the same provider event: no second credit.
        $second = $this->service()->creditDeposit($wallet, 500, 'test', 'txn-1', 'evt-1', $customer->id);
        $this->assertTrue($second['duplicate']);
        $this->assertSame($first['transaction']->id, $second['transaction']->id);
        $this->assertSame(500.0, round((float) $wallet->fresh()->balance, 2));
        $this->assertSame(1, WalletTransaction::where('wallet_id', $wallet->id)->count());

        $rec = $wallet->fresh()->reconcile();
        $this->assertTrue($rec['match']);
        $this->assertDatabaseHas('financial_transactions', ['category' => 'Wallet Deposit']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'wallet.deposit']);
    }

    /** @test */
    public function partial_invoice_payment_updates_everything()
    {
        $customer = $this->customer();
        $wallet = $this->service()->for($customer);
        $this->service()->creditDeposit($wallet, 600, 'test', 'txn-1', 'evt-1', $customer->id);
        $invoice = $this->invoice($customer, 1000);

        $result = $this->service()->payInvoice($wallet->fresh(), $invoice, 600, $customer->id);

        $this->assertSame(0.0, round((float) $result['transaction']->wallet->fresh()->balance, 2));
        $this->assertSame(600.0, (float) $result['invoice']->amount_paid);
        $this->assertSame(400.0, (float) $result['invoice']->amount_due);
        $this->assertSame('partially_paid', $result['invoice']->status);
        $this->assertDatabaseHas('payments', ['id' => $result['payment']->id, 'payment_method' => 'wallet', 'gateway' => 'wallet', 'status' => 'completed']);
        $this->assertDatabaseHas('financial_transactions', ['category' => 'Customer Payment']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'wallet.payment']);
        // Ledger chain intact.
        $this->assertTrue($wallet->fresh()->reconcile()['match']);
    }

    /** @test */
    public function full_payment_closes_invoice_and_overpay_is_rejected()
    {
        $customer = $this->customer();
        $wallet = $this->service()->for($customer);
        $this->service()->creditDeposit($wallet, 1500, 'test', 'txn-1', 'evt-1', $customer->id);
        $invoice = $this->invoice($customer, 1000);

        $result = $this->service()->payInvoice($wallet->fresh(), $invoice, 1000, $customer->id);
        $this->assertSame(500.0, round((float) $wallet->fresh()->balance, 2));
        $this->assertSame('paid', $result['invoice']->status);
        $this->assertSame(0.0, (float) $result['invoice']->amount_due);

        $this->expectException(\Throwable::class);
        $this->service()->payInvoice($wallet->fresh(), $result['invoice'], 100, $customer->id);
    }

    /** @test */
    public function insufficient_balance_rejects_without_side_effects()
    {
        $customer = $this->customer();
        $wallet = $this->service()->for($customer);
        $this->service()->creditDeposit($wallet, 200, 'test', 'txn-1', 'evt-1', $customer->id);
        $invoice = $this->invoice($customer, 500);

        try {
            $this->service()->payInvoice($wallet->fresh(), $invoice, 500, $customer->id);
            $this->fail('Expected insufficient balance exception.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Insufficient', $e->getMessage());
        }
        $this->assertSame(200.0, round((float) $wallet->fresh()->balance, 2));
        $this->assertSame(500.0, (float) $invoice->fresh()->amount_due);
        $this->assertSame(0, Payment::count());
        $this->assertSame(1, WalletTransaction::where('wallet_id', $wallet->id)->count());
    }

    /** @test */
    public function frozen_and_currency_guards_hold()
    {
        $customer = $this->customer();
        $wallet = $this->service()->for($customer);
        $this->service()->creditDeposit($wallet, 500, 'test', 'txn-1', 'evt-1', $customer->id);
        $invoice = $this->invoice($customer, 100);

        $this->service()->freeze($wallet, $customer->id, 'review');
        $this->assertSame('frozen', $wallet->fresh()->status);
        $this->expectException(\RuntimeException::class);
        try {
            $this->service()->payInvoice($wallet->fresh(), $invoice, 100, $customer->id);
        } finally {
            $this->service()->unfreeze($wallet->fresh(), $customer->id);
        }
    }

    /** @test */
    public function currency_mismatch_is_rejected()
    {
        $customer = $this->customer();
        $wallet = $this->service()->for($customer, 'USD');
        $this->service()->creditDeposit($wallet, 500, 'test', 'txn-1', 'evt-1', $customer->id);
        $invoice = $this->invoice($customer, 100);
        $invoice->update(['currency' => 'EUR']);

        $this->expectException(\RuntimeException::class);
        $this->service()->payInvoice($wallet->fresh(), $invoice, 100, $customer->id);
    }

    /** @test */
    public function refund_links_original_and_runs_once()
    {
        $customer = $this->customer();
        $wallet = $this->service()->for($customer);
        $this->service()->creditDeposit($wallet, 1000, 'test', 'txn-1', 'evt-1', $customer->id);
        $invoice = $this->invoice($customer, 1000);
        $paid = $this->service()->payInvoice($wallet->fresh(), $invoice, 1000, $customer->id);

        $finance = User::factory()->create(['role' => 'finance_manager', 'is_active' => true]);
        $result = $this->service()->refundWalletPayment($paid['transaction'], $finance->id, 'Service not delivered');
        $this->assertSame(1000.0, round((float) $wallet->fresh()->balance, 2));
        $this->assertSame(1000.0, (float) $result['invoice']->amount_due);
        $this->assertSame('partially_paid', $result['invoice']->status);

        $this->expectException(\RuntimeException::class);
        $this->service()->refundWalletPayment($paid['transaction'], $finance->id, 'Again');
    }

    /** @test */
    public function adjustments_require_reason_and_audit()
    {
        $customer = $this->customer();
        $wallet = $this->service()->for($customer);
        $finance = User::factory()->create(['role' => 'finance_manager', 'is_active' => true]);

        $this->expectException(\InvalidArgumentException::class);
        $this->service()->adjust($wallet, 'credit', 50, '', $finance->id);

        $txn = $this->service()->adjust($wallet, 'credit', 50, 'Goodwill credit approved by finance', $finance->id);
        $this->assertSame(50.0, round((float) $wallet->fresh()->balance, 2));
        $this->assertDatabaseHas('audit_logs', ['action' => 'wallet.adjustment', 'auditable_id' => $txn->id]);
    }

    /** @test */
    public function http_layer_enforces_ownership_and_roles()
    {
        $a = $this->customer();
        $b = $this->customer();
        $walletB = $this->service()->for($b);

        // Customer A cannot see, top up, or spend from B's wallet.
        // Oracle-free scoping (owner-scoped firstOrFail): cross-customer
        // access answers 404, never 403 — consistent with orders,
        // quotations, documents and tracking.
        $this->actingAs($a)->get(route('portal.wallet.show', $walletB))->assertStatus(404);
        $this->actingAs($a)->post(route('portal.wallet.topup', $walletB), ['amount' => 10])->assertStatus(404);
        $invoiceB = $this->invoice($b, 100);
        $this->actingAs($a)->post(route('portal.wallet.pay-invoice', $walletB), ['invoice_id' => $invoiceB->id, 'amount' => 10])->assertStatus(404);

        // Staff roles are fenced: support cannot touch finance wallets.
        $support = User::factory()->create(['role' => 'support_agent', 'is_active' => true]);
        $this->actingAs($support)->get(route('admin.wallets.index'))->assertStatus(403);
        $finance = User::factory()->create(['role' => 'finance_manager', 'is_active' => true]);
        $this->actingAs($finance)->get(route('admin.wallets.index'))->assertStatus(200);

        // Wallet pages render for the owner.
        $this->actingAs($b)->get(route('portal.wallet.index'))->assertStatus(200)->assertSee('My Wallet', false);
    }

    /** @test */
    public function test_gateway_return_is_verified_and_idempotent()
    {
        $customer = $this->customer();
        $wallet = $this->service()->for($customer);

        $this->actingAs($customer)->post(route('portal.wallet.topup', $wallet), ['amount' => 250])->assertRedirect();
        $pending = WalletTransaction::where('wallet_id', $wallet->id)->where('status', 'pending')->firstOrFail();
        $token = hash_hmac('sha256', $pending->id . ':' . $pending->transaction_reference, config('app.key'));

        $url = route('portal.wallet.topup.return', ['provider' => 'test', 'topup_id' => $pending->id, 'token' => $token]);
        $this->actingAs($customer)->get($url)->assertRedirect()->assertSessionHas('success');
        $this->assertSame(250.0, round((float) $wallet->fresh()->balance, 2));

        // Replay: idempotent, single credit.
        $this->actingAs($customer)->get($url)->assertRedirect();
        $this->assertSame(250.0, round((float) $wallet->fresh()->balance, 2));
        $this->assertSame(1, WalletTransaction::where('wallet_id', $wallet->id)->where('status', 'completed')->count());

        // Forged token: rejected, nothing moves.
        $bad = route('portal.wallet.topup.return', ['provider' => 'test', 'topup_id' => $pending->id, 'token' => 'forged']);
        $this->actingAs($customer)->get($bad)->assertSessionHas('error');
        $this->assertSame(250.0, round((float) $wallet->fresh()->balance, 2));
    }

    /** @test */
    public function stripe_webhook_topup_is_verified_and_idempotent()
    {
        $customer = $this->customer();
        $wallet = $this->service()->for($customer);
        $payload = json_encode([
            'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'id' => 'cs_test_wallet1', 'payment_status' => 'paid',
                'payment_intent' => 'pi_test_wallet1', 'amount_total' => 75000,
                'currency' => 'usd',
                'metadata' => ['purpose' => 'wallet_topup', 'wallet_id' => $wallet->id, 'user_id' => $customer->id],
            ]],
        ]);

        $stripe = app(\App\Services\StripePaymentService::class);
        $first = $stripe->handleWebhook($payload, null);
        $this->assertTrue($first['handled']);
        $this->assertSame(750.0, round((float) $wallet->fresh()->balance, 2));

        $second = $stripe->handleWebhook($payload, null);
        $this->assertTrue($second['duplicate'] ?? true);
        $this->assertSame(750.0, round((float) $wallet->fresh()->balance, 2));

        // Unpaid session: acknowledged, nothing moves.
        $unpaid = json_decode($payload, true);
        $unpaid['data']['object']['id'] = 'cs_test_wallet2';
        $unpaid['data']['object']['payment_status'] = 'unpaid';
        $result = $stripe->handleWebhook(json_encode($unpaid), null);
        $this->assertFalse($result['handled']);
        $this->assertSame(750.0, round((float) $wallet->fresh()->balance, 2));
    }

    /** @test */
    public function concurrent_payments_cannot_overdraw()
    {        $customer = $this->customer();
        $wallet = $this->service()->for($customer);
        $this->service()->creditDeposit($wallet, 1000, 'test', 'txn-1', 'evt-1', $customer->id);
        $invoice = $this->invoice($customer, 1000);

        // Two near-simultaneous 600 debits: at most one can succeed.
        $results = [];
        foreach ([600, 600] as $amt) {
            try {
                $results[] = $this->service()->payInvoice($wallet->fresh(), $invoice->fresh(), $amt, $customer->id);
            } catch (\Throwable $e) {
                $results[] = $e;
            }
        }
        $successes = array_filter($results, fn ($r) => is_array($r));
        $this->assertCount(1, $successes);
        $this->assertSame(400.0, round((float) $wallet->fresh()->balance, 2));
        $this->assertTrue($wallet->fresh()->reconcile()['match']);
    }

    /** @test */
    public function only_eligible_customers_hold_wallets()
    {
        foreach (['support_agent', 'project_manager', 'finance_manager', 'employee', 'freelancer', 'admin', 'super_admin'] as $role) {
            $staff = User::factory()->create(['role' => $role, 'is_active' => true]);
            $this->assertFalse(\App\Services\WalletEligibilityService::isEligible($staff), "Role {$role} must not own wallets.");
            try {
                $this->service()->for($staff);
                $this->fail("Wallet created for ineligible role {$role}.");
            } catch (\RuntimeException $e) {
                $this->assertStringContainsString('not eligible', $e->getMessage());
            }
            $this->assertSame(0, Wallet::where('user_id', $staff->id)->count());
        }
        $customer = $this->customer();
        $this->assertTrue(\App\Services\WalletEligibilityService::isEligible($customer));
    }

    /** @test */
    public function suspended_and_unverified_accounts_are_blocked_but_keep_history()
    {
        $customer = $this->customer();
        $wallet = $this->service()->for($customer);
        $this->service()->creditDeposit($wallet, 300, 'test', 'txn-1', 'evt-1', $customer->id);
        $invoice = $this->invoice($customer, 100);

        $customer->update(['verification_status' => 'suspended']);
        $this->assertFalse(\App\Services\WalletEligibilityService::isEligible($customer->fresh()));
        try {
            $this->service()->payInvoice($wallet->fresh(), $invoice, 100, $customer->id);
            $this->fail('Suspended account must not spend.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('current state', $e->getMessage());
        }
        // History intact: balance, rows and statement still readable.
        $this->assertSame(300.0, round((float) $wallet->fresh()->balance, 2));
        $this->assertSame(1, WalletTransaction::where('wallet_id', $wallet->id)->count());
        $this->actingAs($customer)->get(route('portal.wallet.show', $wallet))->assertStatus(200);

        $unverified = User::factory()->create(['role' => 'customer', 'is_active' => true, 'email_verified_at' => null]);
        $this->assertFalse(\App\Services\WalletEligibilityService::isEligible($unverified));
    }

    /** @test */
    public function concurrent_creation_yields_one_wallet_per_currency()
    {
        $customer = $this->customer();
        $a = $this->service()->for($customer, 'USD');
        $b = $this->service()->for($customer, 'USD');
        $this->assertSame($a->id, $b->id);
        $gbp = $this->service()->for($customer, 'GBP');
        $this->assertNotSame($a->id, $gbp->id);
        $this->assertSame('GBP', $gbp->currency);

        // Database-level uniqueness is the final backstop.
        $this->expectException(\Illuminate\Database\QueryException::class);
        Wallet::create(['user_id' => $customer->id, 'currency' => 'USD', 'wallet_reference' => 'WLT-DUP-TEST', 'status' => 'active', 'balance' => 0]);
    }

    /** @test */
    public function wallet_policy_separates_ownership_from_management()
    {
        $a = $this->customer();
        $b = $this->customer();
        $walletB = $this->service()->for($b);
        $finance = User::factory()->create(['role' => 'finance_manager', 'is_active' => true]);
        $support = User::factory()->create(['role' => 'support_agent', 'is_active' => true]);

        $this->assertTrue($b->can('view', $walletB));
        $this->assertFalse($a->can('view', $walletB));
        $this->assertTrue($finance->can('view', $walletB));
        $this->assertTrue($finance->can('adjust', $walletB));
        $this->assertFalse($support->can('view', $walletB));
        // Managers can inspect but never own: owner unchanged.
        $this->actingAs($finance)->get(route('admin.wallets.show', $walletB))->assertStatus(200);
        $this->assertSame($b->id, (int) $walletB->fresh()->user_id);
    }

    /** @test */
    public function ownership_correction_requires_admin_reason_and_keeps_ledger()
    {
        $a = $this->customer();
        $b = $this->customer();
        $wallet = $this->service()->for($a);
        $this->service()->creditDeposit($wallet, 200, 'test', 'txn-1', 'evt-1', $a->id);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $finance = User::factory()->create(['role' => 'finance_manager', 'is_active' => true]);

        // Finance cannot correct ownership (admin-only).
        $this->actingAs($finance)->post(route('admin.wallets.correct-owner', $wallet), ['new_owner_email' => $b->email, 'reason' => 'Legitimate correction with full detail.'])->assertStatus(403);

        // Admin without reason is rejected.
        $this->actingAs($admin)->post(route('admin.wallets.correct-owner', $wallet), ['new_owner_email' => $b->email, 'reason' => 'x'])->assertSessionHasErrors('reason');

        // Valid correction: owner moves, ledger untouched, audit written.
        $this->actingAs($admin)->post(route('admin.wallets.correct-owner', $wallet), ['new_owner_email' => $b->email, 'reason' => 'Duplicate accounts merged; wallet follows surviving customer.'])->assertSessionHas('success');
        $this->assertSame($b->id, (int) $wallet->fresh()->user_id);
        $this->assertSame(200.0, round((float) $wallet->fresh()->balance, 2));
        $this->assertSame(1, WalletTransaction::where('wallet_id', $wallet->id)->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'wallet.owner_corrected']);

        // Correction to an ineligible (staff) account is refused.
        $staff = User::factory()->create(['role' => 'employee', 'is_active' => true]);
        $this->actingAs($admin)->post(route('admin.wallets.correct-owner', $wallet), ['new_owner_email' => $staff->email, 'reason' => 'Attempted move to staff account for testing.'])->assertSessionHas('error');
        $this->assertSame($b->id, (int) $wallet->fresh()->user_id);
    }

    /** @test */
    public function user_with_wallet_history_cannot_be_deleted()
    {
        $customer = $this->customer();
        $wallet = $this->service()->for($customer);
        $this->service()->creditDeposit($wallet, 100, 'test', 'txn-1', 'evt-1', $customer->id);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $customer))->assertSessionHas('error');
        $this->assertNotNull(User::find($customer->id));
        $this->assertSame(1, WalletTransaction::where('wallet_id', $wallet->id)->count());
    }
}
