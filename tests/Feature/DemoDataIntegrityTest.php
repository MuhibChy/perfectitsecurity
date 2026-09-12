<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Demo data integrity: runs DemoDataSeeder on an isolated database and
 * proves every field persists, calculations are independently correct,
 * validation rejects bad input, search/filter work, relationships chain,
 * role isolation holds, uploads behave, and cleanup removes only demo rows.
 */
class DemoDataIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        $this->seed(DemoDataSeeder::class);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    /** @test */
    public function seeder_is_idempotent_and_marks_everything_demo()
    {
        $before = [
            'users' => User::count(),
            'leads' => \App\Models\Lead::count(),
            'invoices' => Invoice::count(),
        ];
        $this->seed(DemoDataSeeder::class);
        $this->assertEquals($before['users'], User::count());
        $this->assertEquals($before['leads'], \App\Models\Lead::count());
        $this->assertEquals($before['invoices'], Invoice::count());

        $this->assertEquals(5, \App\Models\Lead::where('is_demo', true)->count());
        $this->assertEquals(10, User::where('email', 'like', 'demo.%@example.test')->count());
        $this->assertEquals(10, User::where('is_demo', true)->count());
        $this->assertEquals(5, \App\Models\KbArticle::where('is_demo', true)->count());
        // Visibility distribution preserved across tiers + one draft.
        $this->assertEquals(
            ['admin', 'customer', 'employee', 'public'],
            \App\Models\KbArticle::where('is_demo', true)->where('is_published', true)->orderBy('visibility')->pluck('visibility')->all()
        );
    }

    /** @test */
    public function quotation_totals_match_independent_recomputation()
    {
        $quotes = Quotation::where('is_demo', true)->with('items')->get();
        $this->assertCount(5, $quotes);
        foreach ($quotes as $q) {
            $subtotal = $q->items->sum(fn ($i) => ($i->quantity * $i->unit_price) - $i->discount);
            $tax = $subtotal * ($q->tax_rate / 100);
            $this->assertEquals(round($subtotal, 2), round((float) $q->subtotal, 2), "subtotal {$q->id}");
            $this->assertEquals(round($tax, 2), round((float) $q->tax_amount, 2), "tax {$q->id}");
            $this->assertEquals(round($subtotal + $tax, 2), round((float) $q->total, 2), "total {$q->id}");
        }
    }

    /** @test */
    public function invoice_math_covers_fixed_and_percentage_discounts()
    {
        $invoices = Invoice::where('is_demo', true)->with('items')->get();
        $this->assertCount(5, $invoices);
        $types = [];
        foreach ($invoices as $inv) {
            $subtotal = $inv->items->sum(fn ($i) => ($i->quantity * $i->unit_price) - $i->discount);
            $discount = $inv->discount_type === 'percentage'
                ? $subtotal * ($inv->discount_amount / 100)
                : (float) $inv->discount_amount;
            $after = $subtotal - $discount;
            $tax = $after * ($inv->tax_rate / 100);
            $this->assertEquals(round($subtotal, 2), round((float) $inv->subtotal, 2));
            $this->assertEquals(round($after + $tax, 2), round((float) $inv->total, 2));
            $this->assertEquals(round($inv->total - $inv->amount_paid, 2), round((float) $inv->amount_due, 2));
            $types[] = $inv->discount_type;
        }
        $this->assertContains('fixed', $types);
        $this->assertContains('percentage', $types);
    }

    /** @test */
    public function relationships_chain_across_the_sales_pipeline()
    {
        $customer = User::where('email', 'demo.customer.001@example.test')->firstOrFail();
        $this->assertNotNull($customer->company);

        $lead = \App\Models\Lead::where('email', 'demo.lead.005@example.test')->firstOrFail();
        $this->assertEquals($customer->id, $lead->customer_id); // converted lead
        $this->assertNotNull($lead->converted_at);

        $quote = Quotation::where('customer_id', $customer->id)->firstOrFail();
        $this->assertGreaterThanOrEqual(1, $quote->items->count());

        $proposal = \App\Models\Proposal::where('quotation_id', $quote->id)->first();
        $this->assertNotNull($proposal);
        $this->assertGreaterThanOrEqual(1, $proposal->sections->count());

        $project = \App\Models\Project::where('customer_id', $customer->id)->firstOrFail();
        $this->assertEquals(2, $project->milestones()->count());
        $this->assertEquals($customer->company->id, $customer->company_id);
    }

    /** @test */
    public function validation_rejects_invalid_input()
    {
        $admin = $this->admin();
        $customer = User::where('email', 'demo.customer.001@example.test')->firstOrFail();

        // Quotation requires items.
        $this->actingAs($admin)->post(route('admin.quotations.store'), [
            'customer_id' => $customer->id, 'valid_until' => now()->addDays(7)->toDateString(),
        ])->assertSessionHasErrors('items');

        // Task requires title; invalid priority rejected.
        $this->actingAs($admin)->post(route('admin.tasks.store'), [
            'priority' => 'low', 'type' => 'open',
        ])->assertSessionHasErrors('title');

        $this->actingAs($admin)->post(route('admin.tasks.store'), [
            'title' => 'x', 'priority' => 'nonexistent', 'type' => 'open',
        ])->assertSessionHasErrors('priority');

        // Lead creation rejects malformed email (direct model-level guard via validation path).
        $this->actingAs($admin)->post(route('admin.leads.store'), [
            'name' => 'Bad Email Lead', 'email' => 'not-an-email',
            'status' => 'new', 'priority' => 'low',
        ])->assertSessionHasErrors('email');

        // Over-payment is refused by business logic (422), not silently accepted.
        $invoice = Invoice::where('is_demo', true)->where('status', 'sent')->firstOrFail();
        $this->actingAs($admin)->post(route('admin.payments.store'), [
            'invoice_id' => $invoice->id,
            'amount' => (float) $invoice->amount_due + 1000,
            'payment_method' => 'card',
        ])->assertStatus(422);
    }

    /** @test */
    public function unauthorized_users_cannot_submit_restricted_forms()
    {
        $customer = User::where('email', 'demo.customer.002@example.test')->firstOrFail();
        $this->actingAs($customer)->post(route('admin.quotations.store'), [
            'customer_id' => $customer->id, 'valid_until' => now()->addDays(7)->toDateString(),
            'items' => [['description' => 'x', 'quantity' => 1, 'unit_price' => 1]],
        ])->assertStatus(403);
        $this->actingAs($customer)->get(route('admin.reports.index'))->assertStatus(403);
    }

    /** @test */
    public function search_and_filter_find_demo_records()
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.quotations.index', ['status' => 'sent']))
            ->assertStatus(200)->assertSee('James Carter');
        $invoice = Invoice::where('is_demo', true)->firstOrFail();
        $this->actingAs($admin)->get(route('admin.invoices.index', ['search' => substr($invoice->invoice_number, 0, 12)]))
            ->assertStatus(200)->assertSee($invoice->invoice_number);
        $this->actingAs($admin)->get(route('admin.service-requests.index', ['search' => 'Network assessment']))
            ->assertStatus(200)->assertSee('Network assessment');
    }

    /** @test */
    public function file_upload_accepts_safe_files_and_rejects_executables()
    {
        $customer = User::where('email', 'demo.customer.003@example.test')->firstOrFail();

        $this->actingAs($customer)->post(route('portal.documents.store'), [
            'file' => UploadedFile::fake()->create('demo-notes.txt', 5, 'text/plain'),
            'category' => 'general',
        ])->assertRedirect();
        $this->assertDatabaseHas('customer_documents', ['original_name' => 'demo-notes.txt']);

        $this->actingAs($customer)->post(route('portal.documents.store'), [
            'file' => UploadedFile::fake()->create('evil.exe', 5, 'application/x-msdownload'),
            'category' => 'general',
        ])->assertSessionHasErrors('file');
    }

    /** @test */
    public function demo_documents_download_for_owner_only()
    {
        $owner = User::where('email', 'demo.customer.001@example.test')->firstOrFail();
        $other = User::where('email', 'demo.customer.002@example.test')->firstOrFail();
        $doc = \App\Models\CustomerDocument::where('path', 'like', 'documents/demo-%')
            ->where('user_id', $owner->id)->firstOrFail();

        // Seeded file lives on the real private disk; fake it for download test.
        Storage::disk('private')->put($doc->path, 'demo-bytes');

        $this->actingAs($owner)->get(route('portal.documents.download', $doc))->assertStatus(200);
        $this->actingAs($other)->get(route('portal.documents.download', $doc))->assertStatus(403);
    }

    /** @test */
    public function dashboards_reflect_demo_data()
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertStatus(200);
        $customer = User::where('email', 'demo.customer.001@example.test')->firstOrFail();
        $this->actingAs($customer)->get(route('portal.dashboard'))->assertStatus(200);
        // Sample customer sees their own tickets and invoices listed.
        $this->actingAs($customer)->get(route('portal.tickets.index'))->assertStatus(200)->assertSee('VPN connectivity');
        $this->actingAs($customer)->get(route('portal.invoices.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.reports.profitability'))->assertStatus(200);
    }

    /** @test */
    public function cleanup_removes_only_demo_records()
    {
        // A genuine (non-demo) record must survive cleanup.
        $real = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $realUsers = User::where('email', 'not like', 'demo.%@example.test')->count();
        $this->assertGreaterThan(0, $realUsers);

        $this->artisan('demo:cleanup', ['--confirm' => true])->assertSuccessful();

        $this->assertEquals(0, User::where('is_demo', true)->count());
        $this->assertEquals(0, \App\Models\Lead::where('is_demo', true)->count());
        $this->assertEquals(0, Invoice::where('is_demo', true)->count());
        $this->assertEquals(0, \App\Models\KbArticle::where('is_demo', true)->count());
        // Real records untouched.
        $this->assertEquals($realUsers, User::where('email', 'not like', 'demo.%@example.test')->count());
        $this->assertDatabaseHas('users', ['id' => $real->id]);
    }
}
