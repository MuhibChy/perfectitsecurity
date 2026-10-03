<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Ai\AgentApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Human-approval token tests: bound to exact tool+params, expiring,
 * single-use, tamper-evident. Model output can never constitute approval.
 */
class AgentApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $email = 'appr.admin@example.test'): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true, 'email' => $email]);
    }

    /** @test */
    public function issue_and_verify_round_trip_for_exact_params()
    {
        $admin = $this->admin();
        $svc = app(AgentApprovalService::class);
        $params = ['ticket_number' => 'TK-1', 'action' => 'close'];

        $issued = $svc->issue($admin, 'update_ticket', $params);
        $this->assertArrayHasKey('token', $issued);
        $this->assertTrue($issued['expires_at'] > date('c'));

        $this->assertTrue($svc->verify($admin, $issued['token'], 'update_ticket', $params));
        $this->assertDatabaseHas('audit_logs', ['action' => 'agent.approval_issued']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'agent.approval_consumed']);
    }

    /** @test */
    public function approval_is_single_use()
    {
        $admin = $this->admin('appr.once@example.test');
        $svc = app(AgentApprovalService::class);
        $issued = $svc->issue($admin, 'update_ticket', ['id' => '7']);

        $this->assertTrue($svc->verify($admin, $issued['token'], 'update_ticket', ['id' => '7']));
        $this->assertFalse($svc->verify($admin, $issued['token'], 'update_ticket', ['id' => '7']));
    }

    /** @test */
    public function changed_params_tool_or_approver_invalidate_approval()
    {
        $admin = $this->admin('appr.bind@example.test');
        $other = $this->admin('appr.other@example.test');
        $svc = app(AgentApprovalService::class);

        $a = $svc->issue($admin, 'update_ticket', ['id' => '7']);
        $this->assertFalse($svc->verify($admin, $a['token'], 'update_ticket', ['id' => '8']));
        $b = $svc->issue($admin, 'update_ticket', ['id' => '7']);
        $this->assertFalse($svc->verify($admin, $b['token'], 'refund_payment', ['id' => '7']));
        $c = $svc->issue($admin, 'update_ticket', ['id' => '7']);
        $this->assertFalse($svc->verify($other, $c['token'], 'update_ticket', ['id' => '7']));
        $d = $svc->issue($admin, 'update_ticket', ['id' => '7']);
        $this->assertFalse($svc->verify($admin, substr($d['token'], 0, -4).'AAAA', 'update_ticket', ['id' => '7']));
    }

    /** @test */
    public function non_admin_cannot_verify_high_risk_but_can_verify_non_admin_flow()
    {
        $staff = User::factory()->create(['role' => 'support_agent', 'is_active' => true, 'email' => 'appr.staff@example.test']);
        $svc = app(AgentApprovalService::class);
        $issued = $svc->issue($staff, 'add_ticket_message', ['ticket_number' => 'TK-9']);

        $this->assertFalse($svc->verify($staff, $issued['token'], 'add_ticket_message', ['ticket_number' => 'TK-9'], true));
        $issued2 = $svc->issue($staff, 'add_ticket_message', ['ticket_number' => 'TK-9']);
        $this->assertTrue($svc->verify($staff, $issued2['token'], 'add_ticket_message', ['ticket_number' => 'TK-9'], false));
    }

    /** @test */
    public function inactive_approver_cannot_issue()
    {
        $off = User::factory()->create(['role' => 'admin', 'is_active' => false, 'email' => 'appr.off@example.test']);
        try {
            app(AgentApprovalService::class)->issue($off, 'update_ticket', ['id' => '1']);
            $this->fail('expected 403');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }
}
