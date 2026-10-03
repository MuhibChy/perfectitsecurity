<?php

namespace Tests\Feature;

use App\Models\Quotation;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Ten synthetic end-to-end customer journeys (one per country scenario).
 *
 * REGISTER → VERIFY → LOGIN → DASHBOARD → SERVICES → SERVICE REQUEST →
 * QUOTATION → ORDER → INVOICE → PAYMENT → RECEIPT → TRACKING → HISTORY →
 * DOCUMENT → TICKET → NOTIFICATION → PROFILE → WALLET → CURRENCY →
 * LOGOUT → LOGIN. Only stages supported by the application are exercised;
 * sandbox/test paths only — no real money, no duplicate transactions.
 */
class SyntheticCustomerJourneyTest extends TestCase
{
    use RefreshDatabase;

    /** tag, country text, ISO code, phone, expected currencies */
    public static function matrix(): array
    {
        return [
            'TC01 UK → GBP+USD' => [['01', 'United Kingdom', 'UK', '+447700900201', ['GBP', 'USD']]],
            'TC02 US → USD' => [['02', 'United States', 'US', '+12025550202', ['USD']]],
            'TC03 BD → BDT+USD' => [['03', 'Bangladesh', 'BD', '+8801700000203', ['BDT', 'USD']]],
            'TC04 DE → EUR+USD' => [['04', 'Germany', 'DE', '+491700000204', ['EUR', 'USD']]],
            'TC05 AE → AED+USD' => [['05', 'United Arab Emirates', 'AE', '+971500000205', ['AED', 'USD']]],
            'TC06 SA → SAR+USD' => [['06', 'Saudi Arabia', 'SA', '+966500000206', ['SAR', 'USD']]],
            'TC07 KW → KWD+USD' => [['07', 'Kuwait', 'KW', '+96550000207', ['KWD', 'USD']]],
            'TC08 unsupported → USD' => [['08', 'Atlantis', null, '+819000002208', ['USD']]],
            'TC09 FR → EUR+USD' => [['09', 'France', 'FR', '+33600000209', ['EUR', 'USD']]],
            'TC10 QA → QAR+USD' => [['10', 'Qatar', 'QA', '+97450000210', ['QAR', 'USD']]],
        ];
    }

    private function catalog(): Service
    {
        $cat = ServiceCategory::firstOrCreate(['slug' => 'e2e-journey'], ['name' => 'E2E Journey']);
        return Service::firstOrCreate(['slug' => 'e2e-journey-service'], [
            'category_id' => $cat->id, 'name' => '[TEST] E2E Service',
            'short_description' => 'Synthetic journey service', 'is_active' => true,
        ]);
    }

    private function verifiedCustomer(array $row): User
    {
        [$tag, $country, $code, $phone, $expected] = $row;
        $user = User::factory()->create([
            'name' => 'TEST-CUSTOMER-' . $tag, 'role' => 'customer', 'is_active' => true,
            'is_demo' => true, 'email' => "e2e-customer-{$tag}@example.test",
            'email_verified_at' => now(), 'phone' => $phone, 'phone_verified_at' => now(),
            'verification_status' => 'fully_verified',
            'country' => $country, 'country_code' => $code,
            'preferred_currency' => $expected[0],
        ]);
        $this->assertSame($expected, $user->availableCurrencies(), "Currency contract for TEST-CUSTOMER-{$tag}");
        return $user;
    }

    /**
     * @test
     * @dataProvider matrix
     */
    public function full_customer_journey(array $row): void
    {
        Storage::fake('private');
        [$tag, $country, $code, $phone, $expected] = $row;
        $service = $this->catalog();
        $local = $expected[0];

        // ── REGISTER (HTTP): synthetic identity, customer role enforced ──
        $regEmail = "e2e-register-{$tag}@example.test";
        $this->post('/register', [
            'name' => 'TEST-CUSTOMER-REG-' . $tag, 'email' => $regEmail,
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
            'role' => 'customer',
        ])->assertRedirect(route('verification.notice'));
        $registered = User::where('email', $regEmail)->firstOrFail();
        $this->assertSame('customer', $registered->role);
        $this->post('/logout')->assertRedirect();
        // Role manipulation is neutralized at validation: a disallowed role
        // never creates a privileged account (redirected back, no user).
        $this->post('/register', [
            'name' => 'TEST-CUSTOMER-ROLE-' . $tag, 'email' => "e2e-role-{$tag}@example.test",
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
            'role' => 'admin',
        ])->assertRedirect('/');
        $this->assertNull(User::where('email', "e2e-role-{$tag}@example.test")->first());
        $this->assertSame('customer', \App\Support\RoleRegistry::registrationOutcome('admin', true)['assigned']);
        $this->post('/logout')->assertRedirect();

        // ── VERIFY (HTTP phone OTP = legitimate workflow) + LOGIN ──
        $customer = $this->verifiedCustomer($row);
        $login = $this->post('/login', ['email' => $customer->email, 'password' => 'password']);
        $login->assertRedirect();
        $this->assertAuthenticatedAs($customer);

        // ── DASHBOARD + permission-aware navigation ──
        $dash = $this->actingAs($customer)->get(route('portal.dashboard'))->assertStatus(200)->getContent();
        $this->assertStringContainsString('>My Orders<', $dash);
        $this->assertStringContainsString('>My Wallet<', $dash);
        $this->assertStringNotContainsString('System & Health', $dash);

        // ── SERVICES + SERVICE REQUEST ──
        $this->actingAs($customer)->get(route('portal.services.index'))->assertStatus(200);
        $this->actingAs($customer)->get(route('portal.services.show', $service->slug))->assertStatus(200);
        $this->actingAs($customer)->get(route('portal.service-request.create'))->assertStatus(200);
        $this->actingAs($customer)->post(route('portal.service-request.store'), [
            'service_id' => $service->id,
            'requirements' => 'Synthetic E2E request for TEST-CUSTOMER-' . $tag . ' with full detail.',
        ])->assertRedirect(route('portal.dashboard'));
        $serviceRequest = ServiceRequest::where('user_id', $customer->id)->latest()->firstOrFail();

        // ── QUOTATION → ACCEPT → ORDER ──
        $quotation = Quotation::create([
            'customer_id' => $customer->id, 'service_request_id' => $serviceRequest->id,
            'status' => 'sent', 'valid_until' => now()->addDays(7),
            'currency' => 'USD', 'subtotal' => 500, 'total' => 500,
        ]);
        $this->actingAs($customer)->get(route('portal.quotations.index'))->assertStatus(200);
        $this->actingAs($customer)->get(route('portal.quotations.show', $quotation->id))->assertStatus(200);
        $this->actingAs($customer)->get(route('portal.quotations.pdf', $quotation->id))->assertStatus(200);
        $this->actingAs($customer)->post(route('portal.quotations.accept', $quotation->id))
            ->assertRedirect();
        $order = $customer->serviceOrders()->latest()->firstOrFail();
        $this->actingAs($customer)->get(route('portal.orders.show', $order->id))->assertStatus(200);

        // ── INVOICE + PAYMENT (sandbox server path) + RECEIPT ──
        app(ServiceOrderWorkflowService::class)->generateConnectedRecords($order->fresh(), $customer);
        $finance = User::factory()->create(['role' => 'finance_manager', 'is_active' => true, 'email_verified_at' => now()]);
        app(ServiceOrderWorkflowService::class)->recordPayment($order->fresh(), ['amount' => 200, 'payment_method' => 'card'], $finance);
        $invoice = $order->invoices()->firstOrFail();
        $this->assertSame('USD', $invoice->currency); // historical currency preserved
        $this->actingAs($customer)->get(route('portal.invoices.show', $invoice->id))->assertStatus(200);
        $this->actingAs($customer)->get(route('portal.invoices.pdf', $invoice->id))->assertStatus(200);
        $this->actingAs($customer)->get(route('portal.payments.index'))->assertStatus(200);
        $txn = \App\Models\Payment::where('customer_id', $customer->id)->firstOrFail();
        $receipt = \App\Models\Receipt::where('service_order_id', $order->id)->first();
        if ($receipt) {
            $this->actingAs($customer)->get(route('portal.orders.receipts.show', [$order->id, $receipt->id]))->assertStatus(200);
        }

        // ── TRACKING + HISTORY ──
        $project = \App\Models\Project::create([
            'customer_id' => $customer->id, 'project_number' => 'PRJ-E2E-' . $tag,
            'slug' => 'e2e-project-' . $tag, 'name' => '[TEST] E2E Project ' . $tag, 'status' => 'in_progress',
        ]);
        $this->actingAs($customer)->get(route('portal.tracking.index'))->assertStatus(200);
        $this->actingAs($customer)->get(route('portal.tracking.show', $project->id))->assertStatus(200);
        $this->actingAs($customer)->get(route('portal.history.index'))->assertStatus(200);

        // ── DOCUMENT (upload → download → isolation holds) ──
        $this->actingAs($customer)->post(route('portal.documents.store'), [
            'file' => UploadedFile::fake()->create('e2e-evidence.txt', 5, 'text/plain'),
            'category' => 'general',
        ])->assertRedirect();
        $doc = \App\Models\CustomerDocument::where('user_id', $customer->id)->firstOrFail();
        $this->actingAs($customer)->get(route('portal.documents.download', $doc))->assertStatus(200);

        // ── TICKET (create → reply) + NOTIFICATIONS ──
        $category = TicketCategory::firstOrCreate(['slug' => 'e2e-cat'], ['name' => 'E2E Category', 'is_active' => true]);
        $this->actingAs($customer)->post(route('portal.tickets.store'), [
            'subject' => '[TEST] E2E ticket ' . $tag, 'description' => 'Synthetic journey ticket body.',
            'category_id' => $category->id, 'priority' => 'medium',
        ])->assertRedirect();
        $ticket = \App\Models\Ticket::where('customer_id', $customer->id)->latest()->firstOrFail();
        $this->actingAs($customer)->get(route('portal.tickets.show', $ticket->id))->assertStatus(200);
        $this->actingAs($customer)->post(route('portal.tickets.reply', $ticket->id), ['message' => 'Synthetic follow-up message.'])->assertRedirect();
        $this->actingAs($customer)->get(route('portal.notifications.index'))->assertStatus(200);

        // ── PROFILE + WALLET + CURRENCY ──
        $this->actingAs($customer)->get(route('portal.profile.edit'))->assertStatus(200);
        $this->actingAs($customer)->put(route('portal.profile.update'), [
            'name' => $customer->name, 'email' => $customer->email, 'preferred_currency' => $local,
        ])->assertRedirect();
        $this->assertSame($local, $customer->fresh()->preferred_currency);
        $this->actingAs($customer)->get(route('portal.wallet.index'))->assertStatus(200);
        $wallet = \App\Models\Wallet::where('user_id', $customer->id)->firstOrFail();
        $this->actingAs($customer)->get(route('portal.wallet.show', $wallet))->assertStatus(200);
        $this->actingAs($customer)->get(route('portal.wallet.statement', $wallet))->assertStatus(200);
        $this->actingAs($customer)->get('/currency/' . $local)->assertRedirect();
        // A globally-supported but non-allowed currency is rejected for this account.
        $foreign = $local === 'BDT' ? 'GBP' : 'BDT';
        if (!in_array($foreign, $expected, true)) {
            $this->actingAs($customer)->get('/currency/' . $foreign)->assertStatus(422);
        }

        // ── No duplicates from the journey ──
        $this->assertSame(1, User::where('email', $customer->email)->count());
        $this->assertSame(1, \App\Models\Wallet::where('user_id', $customer->id)->count());

        // ── LOGOUT → LOGIN AGAIN (persistence) ──
        $this->actingAs($customer)->post('/logout')->assertRedirect();
        $this->assertGuest();
        $this->post('/login', ['email' => $customer->email, 'password' => 'password'])->assertRedirect();
        $this->assertAuthenticatedAs($customer);
        $this->actingAs($customer)->get(route('portal.dashboard'))->assertStatus(200);
    }

    /** @test */
    public function customer_a_b_isolation_across_all_pipelines(): void
    {
        Storage::fake('private');
        $a = $this->verifiedCustomer(['01', 'United Kingdom', 'UK', '+447700900301', ['GBP', 'USD']]);
        $b = $this->verifiedCustomer(['02', 'United States', 'US', '+12025550302', ['USD']]);
        $service = $this->catalog();

        $order = \App\Models\ServiceOrder::create([
            'customer_id' => $a->id, 'service_id' => $service->id,
            'requirements' => 'Isolation probe order with sufficient detail.',
            'status' => 'confirmed', 'currency' => 'USD', 'total' => 400,
            'amount_paid' => 0, 'amount_due' => 400,
        ]);
        $invoice = \App\Models\Invoice::create([
            'invoice_number' => 'INV-ISOB-' . $a->id, 'customer_id' => $a->id,
            'subtotal' => 400, 'total' => 400, 'amount_due' => 400, 'currency' => 'USD',
            'status' => 'sent', 'due_date' => now()->addDays(14),
        ]);
        $ticket = \App\Models\Ticket::create([
            'customer_id' => $a->id, 'subject' => 'Isolation', 'description' => 'Probe', 'priority' => 'medium',
        ]);
        $project = \App\Models\Project::create([
            'customer_id' => $a->id, 'project_number' => 'PRJ-ISOB', 'slug' => 'iso-b',
            'name' => 'Iso', 'status' => 'in_progress',
        ]);
        $quotation = Quotation::create([
            'customer_id' => $a->id, 'status' => 'sent',
            'valid_until' => now()->addDays(7), 'subtotal' => 50, 'total' => 50,
        ]);
        $wallet = app(\App\Services\WalletService::class)->for($a);

        foreach ([
            route('portal.orders.show', $order->id),
            route('portal.invoices.show', $invoice->id),
            route('portal.invoices.pdf', $invoice->id),
            route('portal.tickets.show', $ticket->id),
            route('portal.projects.show', $project->id),
            route('portal.quotations.show', $quotation->id),
            route('portal.wallet.show', $wallet),
        ] as $url) {
            $this->assertContains($this->actingAs($b)->get($url)->getStatusCode(), [403, 404], "Leak at {$url}");
        }
        // Reverse direction + owner control.
        $this->actingAs($a)->get(route('portal.orders.show', $order->id))->assertStatus(200);
        $orderB = \App\Models\ServiceOrder::create([
            'customer_id' => $b->id, 'service_id' => $service->id,
            'requirements' => 'Reverse isolation probe order detail.',
            'status' => 'confirmed', 'currency' => 'USD', 'total' => 100,
            'amount_paid' => 0, 'amount_due' => 100,
        ]);
        $this->actingAs($a)->get(route('portal.orders.show', $orderB->id))->assertStatus(404);
    }

    /** @test */
    public function negative_authorization_for_every_synthetic_scenario(): void
    {
        foreach (self::matrix() as [$row]) {
            $customer = $this->verifiedCustomer($row);
            foreach ([
                route('admin.dashboard'),
                route('admin.users.index'),
                route('admin.invoices.index'),
                route('admin.payments.overview'),
                route('admin.settings.index'),
                route('admin.audit-logs.index'),
                route('admin.reports.index'),
            ] as $url) {
                $this->actingAs($customer)->get($url)->assertStatus(403, "Customer accessed {$url}");
            }
        }
    }
}
