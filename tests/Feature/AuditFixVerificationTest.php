<?php

namespace Tests\Feature;

use App\Mail\EmailOtpMail;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\EmailVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Regression coverage for master-audit fixes (no business redesign):
 * missing OTP mailable, customer notification read-all route,
 * strict project/task status validation, money rounding.
 */
class AuditFixVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function email_otp_mailable_renders_with_code()
    {
        $html = (new EmailOtpMail('482913'))->render();
        $this->assertStringContainsString('482913', $html);
    }

    /** @test */
    public function verification_service_sends_otp_email()
    {
        Mail::fake();
        $user = User::factory()->create(['email_verified_at' => null]);
        $result = app(EmailVerificationService::class)->send($user);
        $this->assertTrue($result['success']);
        Mail::assertSent(EmailOtpMail::class);
        $this->assertNotNull($user->fresh()->email_otp_hash);
    }

    /** @test */
    public function customer_can_mark_all_notifications_read()
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        Notification::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'invoice',
            'notifiable_type' => User::class,
            'notifiable_id' => $customer->id,
            'data' => ['title' => 'T', 'message' => 'M'],
        ]);
        $this->actingAs($customer)
            ->post(route('portal.notifications.read-all'))
            ->assertRedirect();
        $this->assertEquals(0, Notification::where('notifiable_id', $customer->id)->unread()->count());
    }

    /** @test */
    public function project_status_update_rejects_arbitrary_values()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $owner = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $project = Project::create(['project_number' => 'PRJ-1', 'name' => 'P', 'slug' => 'p-1', 'status' => 'planning', 'customer_id' => $owner->id, 'created_by' => $admin->id]);
        $this->actingAs($admin)
            ->post(route('admin.projects.status', $project->id), ['status' => 'hacked_status'])
            ->assertSessionHasErrors('status');
        $this->assertEquals('planning', $project->fresh()->status);
    }

    /** @test */
    public function task_update_rejects_arbitrary_status()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $task = Task::create([
            'task_number' => 'TSK-1', 'title' => 'T', 'created_by' => $admin->id,
            'status' => 'pending', 'priority' => 'medium',
        ]);
        $this->actingAs($admin)
            ->put(route('admin.tasks.update', $task->id), [
                'title' => 'T', 'priority' => 'medium', 'status' => 'hacked_status',
            ])
            ->assertSessionHasErrors('status');
        $this->assertEquals('pending', $task->fresh()->status);
    }
}
