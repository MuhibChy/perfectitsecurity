<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PhoneVerificationService;
use App\Support\PhoneCountries;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Global mobile verification: one authoritative ISO country registry,
 * correct dial codes, canonical E.164 storage, explicit verification
 * states, and no duplicate phone identities.
 */
class PhoneCountryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function registry_covers_the_global_dataset_with_unique_codes()
    {
        $all = PhoneCountries::all();
        $this->assertGreaterThanOrEqual(200, count($all));
        $alpha2 = array_column($all, 'alpha2');
        $this->assertSame(count($alpha2), count(array_unique($alpha2)), 'Duplicate alpha-2 codes');
        foreach ($all as $entry) {
            $this->assertNotEmpty($entry['name']);
            $this->assertMatchesRegularExpression('/^[A-Z]{2}$/', $entry['alpha2']);
            $this->assertMatchesRegularExpression('/^[A-Z]{3}$/', $entry['alpha3']);
            $this->assertMatchesRegularExpression('/^[1-9][0-9]{0,4}$/', (string) $entry['dial']);
            $this->assertNotEmpty($entry['flag']);
        }
    }

    /** @test */
    public function dial_codes_are_correct_for_key_markets()
    {
        $expected = [
            'GB' => '44', 'US' => '1', 'BD' => '880', 'IN' => '91', 'PK' => '92',
            'CA' => '1', 'AU' => '61', 'DE' => '49', 'FR' => '33', 'IT' => '39',
            'ES' => '34', 'AE' => '971', 'SA' => '966', 'QA' => '974', 'SG' => '65',
            'MY' => '60', 'JP' => '81', 'CN' => '86', 'ZA' => '27', 'BR' => '55',
            'MX' => '52', 'KW' => '965', 'BH' => '973', 'OM' => '968', 'JO' => '962',
        ];
        foreach ($expected as $code => $dial) {
            $this->assertSame($dial, PhoneCountries::dialCodeFor($code), "Dial code for {$code}");
        }
        // Billing alias: app stores United Kingdom as UK.
        $this->assertSame('44', PhoneCountries::dialCodeFor('UK'));
        $this->assertNull(PhoneCountries::dialCodeFor('XX'));
    }

    /** @test */
    public function national_numbers_normalize_to_canonical_e164()
    {
        $svc = app(PhoneVerificationService::class);
        $this->assertSame('+447123456789', $svc->normalizeForCountry('07123456789', 'GB'));
        $this->assertSame('+447123456789', $svc->normalizeForCountry('7123456789', 'GB'));
        $this->assertSame('+8801700000103', $svc->normalizeForCountry('01700000103', 'BD'));
        $this->assertSame('+12025550102', $svc->normalizeForCountry('2025550102', 'US'));
        $this->assertSame('+919876543210', $svc->normalizeForCountry('09876543210', 'IN'));
        // A leading + always wins (already international).
        $this->assertSame('+491700000104', $svc->normalizeForCountry('+491700000104', 'BD'));
        // Unknown country and empty numbers are rejected, not guessed.
        try {
            $svc->normalizeForCountry('7123456789', 'XX');
            $this->fail('Unknown country accepted');
        } catch (\Throwable $e) {
            $this->assertSame(422, $e->getStatusCode());
        }
        try {
            $svc->normalizeForCountry('0000', 'GB');
            $this->fail('Empty number accepted');
        } catch (\Throwable $e) {
            $this->assertSame(422, $e->getStatusCode());
        }
    }

    /** @test */
    public function phone_states_are_explicit_and_distinct()
    {
        $svc = app(PhoneVerificationService::class);
        $base = ['role' => 'customer', 'is_active' => true, 'email_verified_at' => now()];
        $this->assertSame('NOT_PROVIDED', $svc->phoneStateFor(User::factory()->create($base + ['phone' => null])));
        $this->assertSame('VERIFIED', $svc->phoneStateFor(User::factory()->create($base + ['phone' => '+447700900111', 'phone_verified_at' => now()])));
        $this->assertSame('PROVIDED', $svc->phoneStateFor(User::factory()->create($base + ['phone' => '+447700900112'])));
        $this->assertSame('FAILED', $svc->phoneStateFor(User::factory()->create($base + ['phone' => '+447700900113', 'phone_otp_hash' => 'x', 'phone_otp_attempts' => 5])));
        $this->assertSame('EXPIRED', $svc->phoneStateFor(User::factory()->create($base + ['phone' => '+447700900114', 'phone_otp_hash' => 'x', 'phone_otp_attempts' => 0, 'phone_otp_expires_at' => now()->subMinute()])));
        $pending = User::factory()->create($base + ['phone' => '+447700900115', 'phone_otp_hash' => 'x', 'phone_otp_attempts' => 0, 'phone_otp_expires_at' => now()->addMinutes(10), 'phone_otp_sent_at' => now()]);
        $this->assertSame('PENDING', $svc->phoneStateFor($pending));
    }

    /** @test */
    public function verification_accepts_country_plus_national_number()
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true, 'email_verified_at' => now(), 'phone' => null]);
        $this->actingAs($customer)->post(route('portal.verification.phone.send'), [
            'country' => 'GB', 'national_number' => '07700900121',
        ])->assertRedirect();
        $this->assertSame('+447700900121', $customer->fresh()->phone);

        // Unknown country rejected; duplicate verified numbers rejected.
        $other = User::factory()->create(['role' => 'customer', 'is_active' => true, 'email_verified_at' => now()]);
        $this->actingAs($other)->post(route('portal.verification.phone.send'), [
            'country' => 'XX', 'national_number' => '07700900122',
        ])->assertStatus(422);
    }

    /** @test */
    public function same_number_in_any_format_is_one_identity()
    {
        $svc = app(PhoneVerificationService::class);
        $a = User::factory()->create(['role' => 'customer', 'email_verified_at' => now(), 'phone' => null]);
        $result = $svc->start($a, '+447700900131');
        $this->assertTrue($svc->verify($a->fresh(), $result['code']));
        $this->assertSame('+447700900131', $a->fresh()->phone);

        // National-format duplicate of the same verified number is refused.
        $b = User::factory()->create(['role' => 'customer', 'email_verified_at' => now(), 'phone' => null]);
        try {
            $svc->start($b, $svc->normalizeForCountry('07700900131', 'GB'));
            $this->fail('Duplicate identity created');
        } catch (\Throwable $e) {
            $this->assertSame(422, $e->getStatusCode());
        }
        $this->assertNull($b->fresh()->phone_verified_at);
    }

    /** @test */
    public function verification_ui_renders_the_global_selector()
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true, 'email_verified_at' => now(), 'phone' => null]);
        $content = $this->actingAs($customer)->get(route('portal.verification.phone'))->getContent();
        $this->assertStringContainsString('country_search', $content);
        $this->assertStringContainsString('national_number', $content);
        $this->assertStringContainsString('United Kingdom (+44)', $content);
        $this->assertStringContainsString('Bangladesh (+880)', $content);
        $this->assertStringContainsString('NOT_PROVIDED', $content);
    }

    /** @test */
    public function registration_and_profile_accept_country_numbers()
    {
        $this->post('/register', [
            'name' => 'Phone Journey', 'email' => 'phone-journey@example.test',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
            'role' => 'customer', 'country' => 'BD', 'national_number' => '01700000141',
        ])->assertRedirect(route('verification.notice'));
        $user = User::where('email', 'phone-journey@example.test')->firstOrFail();
        $this->assertSame('+8801700000141', $user->phone);
        $this->assertNull($user->phone_verified_at);

        $user->update(['email_verified_at' => now()]);
        $this->actingAs($user)->put(route('portal.profile.update'), [
            'name' => $user->name, 'email' => $user->email,
            'country' => 'GB', 'national_number' => '07700900142',
        ])->assertRedirect();
        $this->assertSame('+447700900142', $user->fresh()->phone);
    }
}
