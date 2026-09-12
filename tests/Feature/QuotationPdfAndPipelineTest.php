<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Service;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuotationPdfAndPipelineTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customer_can_download_quotation_pdf()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $quotation = Quotation::create([
            'customer_id' => $customer->id,
            'status' => 'sent',
            'valid_until' => now()->addDays(14),
            'subtotal' => 1200,
            'total' => 1200,
        ]);
        $quotation->items()->create([
            'description' => 'Cybersecurity Web Pentest',
            'quantity' => 1,
            'unit_price' => 1200,
            'total' => 1200,
        ]);

        $response = $this->actingAs($customer)->get(route('portal.quotations.pdf', $quotation->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function other_customer_cannot_download_foreign_quotation_pdf()
    {
        $customer1 = User::factory()->create(['role' => 'customer']);
        $customer2 = User::factory()->create(['role' => 'customer']);

        $quotation = Quotation::create([
            'customer_id' => $customer1->id,
            'status' => 'sent',
            'subtotal' => 500,
            'total' => 500,
        ]);

        $response = $this->actingAs($customer2)->get(route('portal.quotations.pdf', $quotation->id));

        $response->assertStatus(404);
    }

    /** @test */
    public function admin_can_download_quotation_pdf()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        $quotation = Quotation::create([
            'customer_id' => $customer->id,
            'status' => 'sent',
            'subtotal' => 850,
            'total' => 850,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.quotations.pdf', $quotation->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function staff_can_view_visual_sales_pipeline()
    {
        $staff = User::factory()->create(['role' => 'admin']);

        ServiceRequest::create([
            'name' => 'Acme Corp',
            'email' => 'tech@acme.com',
            'requirements' => 'Cloud migration audit',
            'budget' => 3500,
            'review_status' => 'new',
            'priority' => 'high',
        ]);

        $response = $this->actingAs($staff)->get(route('admin.service-requests.pipeline'));

        $response->assertStatus(200);
        $response->assertSee('Visual Sales Pipeline');
        $response->assertSee('Acme Corp');
        $response->assertSee('3,500');
    }

    /** @test */
    public function staff_can_update_service_request_stage()
    {
        $staff = User::factory()->create(['role' => 'admin']);

        $sr = ServiceRequest::create([
            'name' => 'Globex Systems',
            'email' => 'sales@globex.com',
            'requirements' => 'Need 24/7 Managed IT support',
            'review_status' => 'new',
        ]);

        $response = $this->actingAs($staff)->post(route('admin.service-requests.update-status', $sr->id), [
            'review_status' => 'under_review',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('service_requests', [
            'id' => $sr->id,
            'review_status' => 'under_review',
        ]);
    }

    /** @test */
    public function staff_can_graduate_service_request_to_quotation()
    {
        $staff = User::factory()->create(['role' => 'admin']);
        $category = \App\Models\ServiceCategory::create([
            'name' => 'Cybersecurity',
            'slug' => 'cybersecurity',
            'description' => 'Security testing and operations',
        ]);

        $service = Service::create([
            'category_id' => $category->id,
            'name' => 'Endpoint Security Monitoring',
            'slug' => 'endpoint-security-monitoring',
            'short_description' => 'EDR and SOC protection',
            'starting_price' => 1500,
            'is_active' => true,
        ]);

        $sr = ServiceRequest::create([
            'service_id' => $service->id,
            'name' => 'Initech Corp',
            'email' => 'peter@initech.com',
            'requirements' => 'Deploy SOC for 50 workstations',
            'budget' => 2000,
            'review_status' => 'new',
        ]);

        $response = $this->actingAs($staff)->post(route('admin.service-requests.graduate', $sr->id));

        $response->assertRedirect();
        $sr->refresh();

        $this->assertEquals('quoted', $sr->review_status);
        $this->assertNotNull($sr->quotation_id);

        $quotation = Quotation::find($sr->quotation_id);
        $this->assertNotNull($quotation);
        $this->assertEquals(2000, $quotation->total);
        $this->assertCount(1, $quotation->items);
        $this->assertEquals('Endpoint Security Monitoring', $quotation->items->first()->description);
    }
}
