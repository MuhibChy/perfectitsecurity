<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Services\CommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionTest extends TestCase
{
    use RefreshDatabase;

    protected $worker;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->worker = User::create([
            'name' => 'Test Worker',
            'email' => 'worker@test.com',
            'password' => bcrypt('password'),
            'role' => 'freelancer',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $this->admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    public function test_percentage_commission_calculation()
    {
        $rule = CommissionRule::create(['name' => 'Standard', 'type' => 'percentage', 'rate' => 10, 'is_active' => true]);

        $service = app(CommissionService::class);
        $commission = $service->calculateCommission($this->worker->id, 1000, $rule->id);

        $this->assertEquals(100, $commission->commission_amount);
        $this->assertEquals(10, $commission->commission_rate);
        $this->assertEquals('pending', $commission->status);
    }

    public function test_fixed_commission_calculation()
    {
        $rule = CommissionRule::create(['name' => 'Fixed', 'type' => 'fixed', 'rate' => 50, 'is_active' => true]);

        $service = app(CommissionService::class);
        $commission = $service->calculateCommission($this->worker->id, 1000, $rule->id);

        $this->assertEquals(50, $commission->commission_amount);
    }

    public function test_commission_approval()
    {
        $rule = CommissionRule::create(['name' => 'Test', 'type' => 'percentage', 'rate' => 10, 'is_active' => true]);

        $service = app(CommissionService::class);
        $commission = $service->calculateCommission($this->worker->id, 500, $rule->id);

        $service->approveCommission($commission, $this->admin->id);
        $commission->refresh();

        $this->assertEquals('approved', $commission->status);
        $this->assertNotNull($commission->approved_at);
    }

    public function test_commission_rejection()
    {
        $rule = CommissionRule::create(['name' => 'Test', 'type' => 'percentage', 'rate' => 10, 'is_active' => true]);

        $service = app(CommissionService::class);
        $commission = $service->calculateCommission($this->worker->id, 500, $rule->id);

        $service->rejectCommission($commission, $this->admin->id, 'Not qualified');
        $commission->refresh();

        $this->assertEquals('rejected', $commission->status);
    }

    public function test_worker_earnings_tracking()
    {
        $rule = CommissionRule::create(['name' => 'Test', 'type' => 'percentage', 'rate' => 10, 'is_active' => true]);

        $service = app(CommissionService::class);
        $service->calculateCommission($this->worker->id, 1000, $rule->id);
        $service->calculateCommission($this->worker->id, 2000, $rule->id);

        $earnings = $service->getWorkerEarnings($this->worker->id);

        $this->assertEquals(300, $earnings['total_earned']);
        $this->assertEquals(300, $earnings['pending']);
    }
}
