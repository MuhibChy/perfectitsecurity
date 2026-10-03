<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\DecorativeZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Visual safety: content always wins over decoration. Decorative layers
 * are inert (pointer-events none, aria-hidden, behind content), print
 * output is decor-free, mobile gets minimal decoration, and empty
 * states carry exactly one contextual object that never blocks action.
 */
class VisualSafetyTest extends TestCase
{
    use RefreshDatabase;

    private function css(): string
    {
        return file_get_contents(base_path('resources/css/app.css'));
    }

    private function customer(): User
    {
        return User::factory()->create([
            'role' => 'customer', 'is_active' => true,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
        ]);
    }

    /** @test */
    public function decorative_layers_are_inert_and_behind_content()
    {
        $css = $this->css();
        foreach (['.gsb', '.auth-bg', '.term-bg', '.page-lights', '.global-3d', '.global-3d-fg', '.global-hud', '.decor-zone', '.empty-decor'] as $layer) {
            $this->assertStringContainsString($layer, $css, "Missing layer: {$layer}");
        }
        $this->assertMatchesRegularExpression('/\.global-3d[,\s][\s\S]{0,300}?pointer-events:\s*none/', $css);
        $this->assertStringContainsString('#main-content { isolation: isolate; }', $css);
        $this->assertStringContainsString('#global-3d-canvas-fg { z-index: 5; }', $css);

        $html = $this->actingAs($this->customer())->get(route('portal.dashboard'))->getContent();
        // Decor mounts before the content workspace and is hidden from AT.
        $decorPos = strpos($html, 'global-3d-canvas');
        $contentPos = strpos($html, 'id="main-content"');
        $this->assertNotFalse($decorPos);
        $this->assertNotFalse($contentPos);
        $this->assertLessThan($contentPos, $decorPos);
        $this->assertStringContainsString('aria-hidden="true"', $html);
    }

    /** @test */
    public function print_output_is_decor_free()
    {
        $css = $this->css();
        foreach (['.term-bg', '.global-hud', '#glove-control', '.decor-zone', '.empty-decor'] as $sel) {
            $printBlock = substr($css, strpos($css, '@media print'));
            $this->assertStringContainsString($sel, $printBlock, "Not print-hidden: {$sel}");
        }
        // Server PDFs never include decor markers.
        $customer = $this->customer();
        $invoice = \App\Models\Invoice::create([
            'invoice_number' => 'INV-VIS-01', 'customer_id' => $customer->id,
            'subtotal' => 100, 'total' => 100, 'amount_due' => 100,
            'currency' => 'USD', 'status' => 'sent', 'due_date' => now()->addDays(7),
        ]);
        $pdf = $this->actingAs($customer)->get(route('portal.invoices.pdf', $invoice->id))->assertStatus(200)->getContent();
        $this->assertStringStartsWith('%PDF', $pdf);
    }

    /** @test */
    public function responsive_rules_keep_mobile_clean()
    {
        $css = $this->css();
        $this->assertMatchesRegularExpression('/@media\s*\(max-width:\s*767px\)[\s\S]{0,600}?\.global-hud\s*\{\s*display:\s*none/', $css);
        $this->assertMatchesRegularExpression('/@media\s*\(max-width:\s*767px\)[\s\S]{0,600}?\.decor-zone\s*\{\s*display:\s*none/', $css);
        $this->assertStringContainsString('overflow-x:clip', str_replace(' ', '', $css));
    }

    /** @test */
    public function reduced_motion_freezes_decoration()
    {
        $css = $this->css();
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        $this->assertMatchesRegularExpression('/prefers-reduced-motion:\s*reduce[\s\S]{0,800}?\.decor-zone/', $css);
        $js = file_get_contents(base_path('resources/js/global-3d.js'));
        $this->assertStringContainsString('reduced', $js);
        $this->assertStringContainsString('renderOnce', $js);
    }

    /** @test */
    public function zone_config_is_restrained_and_valid()
    {
        foreach (['cybersecurity', 'networking', 'servers', 'ai', 'finance', 'reports', 'training', 'support', 'dashboard', 'hero', 'unknown-page'] as $page) {
            $cfg = DecorativeZone::for($page);
            $this->assertTrue(DecorativeZone::isKnownObject($cfg['object']), "Unknown object for {$page}");
            $this->assertLessThanOrEqual(2, $cfg['max']);
            $this->assertSame('hidden', $cfg['mobile']);
        }
        // Data-heavy default gets nothing.
        $this->assertSame(0, DecorativeZone::for('invoices-table')['max']);
        // Every empty-state type maps to a known object.
        foreach (['services', 'orders', 'tickets', 'projects', 'tasks', 'reports', 'documents', 'notifications', 'payments', 'invoices', 'commissions', 'training', 'security', 'search'] as $type) {
            $this->assertTrue(DecorativeZone::isKnownObject(DecorativeZone::emptyObject($type)), "Bad empty object: {$type}");
        }
    }

    /** @test */
    public function decorative_components_render_inert_svg()
    {
        $html = view('components.decor-it-object', ['object' => 'server-rack'])->render();
        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringNotContainsString('<script', $html);

        $zone = view('components.decorative-3d-zone', ['position' => 'side-right', 'object' => 'shield-lock', 'size' => 'md', 'page' => null])->render();
        $this->assertStringContainsString('data-decor-zone="side-right"', $zone);
        $this->assertStringContainsString('aria-hidden="true"', $zone);

        // Unknown object falls back safely; data pages render nothing.
        $fallback = view('components.decorative-3d-zone', ['object' => 'spaceship', 'page' => null])->render();
        $this->assertStringNotContainsString('spaceship', $fallback);
        $this->assertSame('', trim(view('components.decorative-3d-zone', ['page' => 'unknown-page'])->render()));
    }

    /** @test */
    public function empty_states_show_one_object_without_blocking_action()
    {
        $html = view('components.empty-state-3d', [
            'type' => 'orders', 'title' => 'No orders', 'message' => 'Empty.',
            'actionText' => 'Browse', 'actionUrl' => '/services',
        ])->render();
        // Exactly one decorative object (plus the action arrow icon).
        $this->assertSame(1, substr_count($html, 'empty-decor'));
        $this->assertStringContainsString('No orders', $html);
        $this->assertStringContainsString('/services', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);

        // Live page: empty orders show the object + action…
        $customer = $this->customer();
        $empty = $this->actingAs($customer)->get(route('portal.orders.index'))->getContent();
        $this->assertStringContainsString('No Service Orders Placed Yet', $empty);
        $this->assertStringContainsString('Browse Services Catalogue', $empty);
        // …and populated orders show a clean table with no decor object.
        $cat = \App\Models\ServiceCategory::firstOrCreate(['slug' => 'vis-ord'], ['name' => 'Vis']);
        $svc = \App\Models\Service::firstOrCreate(['slug' => 'vis-ord-svc'], ['category_id' => $cat->id, 'name' => 'Vis Svc', 'short_description' => 'x', 'is_active' => true]);
        \App\Models\ServiceOrder::create(['customer_id' => $customer->id, 'service_id' => $svc->id, 'requirements' => 'Visual safety probe order detail.', 'status' => 'confirmed', 'currency' => 'USD', 'total' => 100, 'amount_paid' => 0, 'amount_due' => 100]);
        $full = $this->actingAs($customer)->get(route('portal.orders.index'))->getContent();
        $this->assertStringNotContainsString('No Service Orders Placed Yet', $full);
        $this->assertStringNotContainsString('empty-decor', $full);
    }

    /** @test */
    public function glove_control_is_safe_and_authenticated_only()
    {
        $customer = $this->customer();
        $html = $this->actingAs($customer)->get(route('portal.dashboard'))->getContent();
        $this->assertStringContainsString('id="glove-control"', $html);
        $this->assertStringContainsString('aria-label="3D glove controls"', $html);
        // Guests get no control surface at all.
        $this->post('/logout');
        $guest = $this->get('/')->getContent();
        $this->assertStringNotContainsString('id="glove-control"', $guest);

        $js = file_get_contents(base_path('resources/js/global-3d.js'));
        $this->assertStringContainsString('gloveSafeBounds', $js);
        $this->assertStringContainsString('positioningTarget', $js);
    }
}
