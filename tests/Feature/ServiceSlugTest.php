<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceSlugTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    private function category(): ServiceCategory
    {
        return ServiceCategory::firstOrCreate(['slug' => 'slug-cat'], ['name' => 'Slug Cat']);
    }

    private function payload(string $name): array
    {
        return [
            'name' => $name,
            'category_id' => $this->category()->id,
            'short_description' => 'desc',
            'price_type' => 'custom',
        ];
    }

    /** @test */
    public function duplicate_service_names_get_unique_slugs_instead_of_500()
    {
        $this->actingAs($this->admin())->post(route('admin.services.store'), $this->payload('Remote IT Support'))
            ->assertRedirect(route('admin.services.index'));
        $this->actingAs($this->admin())->post(route('admin.services.store'), $this->payload('Remote IT Support'))
            ->assertRedirect(route('admin.services.index'));

        $slugs = Service::where('name', 'Remote IT Support')->orderBy('id')->pluck('slug')->all();
        $this->assertEquals(['remote-it-support', 'remote-it-support-2'], $slugs);
    }

    /** @test */
    public function renaming_to_existing_name_suffixes_slug_and_keeps_own()
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.services.store'), $this->payload('Alpha Service'));
        $this->actingAs($admin)->post(route('admin.services.store'), $this->payload('Beta Service'));

        $beta = Service::where('name', 'Beta Service')->firstOrFail();
        // Saving unchanged name keeps its own slug.
        $this->actingAs($admin)->put(route('admin.services.update', $beta->id), $this->payload('Beta Service'))
            ->assertRedirect();
        $this->assertEquals('beta-service', $beta->fresh()->slug);

        // Renaming onto an existing name gets a suffix, no 500.
        $this->actingAs($admin)->put(route('admin.services.update', $beta->id), $this->payload('Alpha Service'))
            ->assertRedirect();
        $this->assertEquals('alpha-service-2', $beta->fresh()->slug);
    }
}
