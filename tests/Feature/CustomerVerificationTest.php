<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PhoneVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_request_phone_otp_and_verify_successfully(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
            'phone' => null,
            'phone_verified_at' => null,
            'verification_status' => 'pending',
        ]);

        $service = app(PhoneVerificationService::class);
        $result = $service->start($customer, '+12025550123');

        $this->assertTrue($result['success']);
        $customer->refresh();
        $this->assertEquals('+12025550123', $customer->phone);
        $this->assertNotNull($customer->phone_otp_hash);

        // Verification with valid code
        $code = $result['code'];
        $verified = $service->verify($customer, $code);

        $this->assertTrue($verified);
        $customer->refresh();
        $this->assertNotNull($customer->phone_verified_at);
        $this->assertEquals('fully_verified', $customer->verification_status);
        $this->assertTrue($customer->isFullyVerified());
    }

    public function test_phone_otp_enforces_resend_cooldown(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'phone' => '+447123456789',
            'phone_otp_sent_at' => now(),
        ]);

        $service = app(PhoneVerificationService::class);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $service->start($customer, '+447123456789');
    }

    public function test_phone_otp_rejects_expired_code(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'phone' => '+447123456789',
            'phone_otp_hash' => hash('sha256', '123456'),
            'phone_otp_expires_at' => now()->subMinutes(5),
            'phone_otp_attempts' => 0,
        ]);

        $service = app(PhoneVerificationService::class);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $service->verify($customer, '123456');
    }

    public function test_phone_otp_invalidates_after_too_many_attempts(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'phone' => '+447123456789',
            'phone_otp_hash' => hash('sha256', '123456'),
            'phone_otp_expires_at' => now()->addMinutes(10),
            'phone_otp_attempts' => 5,
        ]);

        $service = app(PhoneVerificationService::class);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $service->verify($customer, '123456');
    }

    public function test_cannot_use_phone_number_already_verified_by_another_customer(): void
    {
        User::factory()->create([
            'phone' => '+447999888777',
            'phone_verified_at' => now(),
        ]);

        $newCustomer = User::factory()->create([
            'role' => 'customer',
            'phone' => null,
        ]);

        $service = app(PhoneVerificationService::class);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $service->start($newCustomer, '+447999888777');
    }
}
