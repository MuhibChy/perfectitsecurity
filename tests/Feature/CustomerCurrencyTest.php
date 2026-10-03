<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Location-based currency: at most the supported local currency + USD,
 * USD-only fallback for unsupported countries, no duplicate USD, account
 * persistence, historical-record preservation, and route enforcement.
 */
class CustomerCurrencyTest extends TestCase
{
    use RefreshDatabase;

    private function customer(?string $country, ?string $code): User
    {
        return User::factory()->create([
            'role' => 'customer', 'is_active' => true,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
            'country' => $country, 'country_code' => $code,
        ]);
    }

    /** @test */
    public function currency_matrix_matches_location_rule()
    {
        $this->assertSame(['GBP', 'USD'], $this->customer('United Kingdom', 'UK')->availableCurrencies());
        $this->assertSame(['USD'], $this->customer('United States', 'US')->availableCurrencies());
        $this->assertSame(['BDT', 'USD'], $this->customer('Bangladesh', 'BD')->availableCurrencies());
        $this->assertSame(['EUR', 'USD'], $this->customer('Germany', 'DE')->availableCurrencies());
        $this->assertSame(['AED', 'USD'], $this->customer('United Arab Emirates', 'AE')->availableCurrencies());
        $this->assertSame(['KWD', 'USD'], $this->customer('Kuwait', 'KW')->availableCurrencies());
        // Unsupported country → USD only (never an invented currency).
        $this->assertSame(['USD'], $this->customer('Atlantis', null)->availableCurrencies());
        $this->assertSame(['USD'], $this->customer(null, null)->availableCurrencies());
        // No duplicate USD for the US customer.
        $this->assertCount(1, $this->customer('United States', 'US')->availableCurrencies());
    }

    /** @test */
    public function free_text_country_resolves_without_explicit_code()
    {
        $this->assertSame(['GBP', 'USD'], $this->customer('United Kingdom', null)->availableCurrencies());
        $this->assertSame(['GBP', 'USD'], $this->customer('Great Britain', null)->availableCurrencies());
        $this->assertSame(['BDT', 'USD'], $this->customer('bangladesh', null)->availableCurrencies());
    }

    /** @test */
    public function currency_switch_enforces_customer_allowance()
    {
        $uk = $this->customer('United Kingdom', 'UK');
        $this->actingAs($uk)->get('/currency/GBP')->assertRedirect();
        $this->assertSame('GBP', $uk->fresh()->preferred_currency);
        $this->actingAs($uk)->get('/currency/USD')->assertRedirect();
        // Supported globally but not allowed for this account.
        $this->actingAs($uk)->get('/currency/BDT')->assertStatus(422);
        $this->actingAs($uk)->get('/currency/XXX')->assertStatus(400);
        // Guests keep the public catalog behavior (log out first: actingAs persists).
        $this->post('/logout')->assertRedirect();
        $this->get('/currency/BDT')->assertRedirect();
        $this->get('/currency/XXX')->assertStatus(400);
    }

    /** @test */
    public function profile_currency_validates_against_customer_allowance()
    {
        $uk = $this->customer('United Kingdom', 'UK');
        $this->actingAs($uk)->put(route('portal.profile.update'), [
            'name' => $uk->name, 'email' => $uk->email, 'preferred_currency' => 'GBP',
        ])->assertRedirect();
        $this->assertSame('GBP', $uk->fresh()->preferred_currency);

        $this->actingAs($uk)->put(route('portal.profile.update'), [
            'name' => $uk->name, 'email' => $uk->email, 'preferred_currency' => 'BDT',
        ])->assertStatus(422);
    }

    /** @test */
    public function profile_dropdown_lists_only_allowed_currencies()
    {
        $uk = $this->customer('United Kingdom', 'UK');
        $content = $this->actingAs($uk)->get(route('portal.profile.edit'))->getContent();
        $this->assertStringContainsString('value="GBP"', $content);
        $this->assertStringContainsString('value="USD"', $content);
        $this->assertStringNotContainsString('value="BDT"', $content);

        $us = $this->customer('United States', 'US');
        $content = $this->actingAs($us)->get(route('portal.profile.edit'))->getContent();
        $this->assertSame(1, substr_count($content, 'value="USD"'), 'Duplicate USD option rendered');
    }

    /** @test */
    public function changing_preference_never_mutates_historical_records()
    {
        $uk = $this->customer('United Kingdom', 'UK');
        $invoice = Invoice::create([
            'invoice_number' => 'INV-HIST-GBP', 'customer_id' => $uk->id,
            'subtotal' => 1000, 'total' => 1000, 'amount_due' => 1000,
            'currency' => 'GBP', 'status' => 'sent', 'due_date' => now()->addDays(14),
        ]);
        $this->actingAs($uk)->get('/currency/USD')->assertRedirect();
        $this->assertSame('USD', $uk->fresh()->preferred_currency);
        $this->assertSame('GBP', $invoice->fresh()->currency);
        $this->assertEquals(1000, (float) $invoice->fresh()->total);
    }
}
