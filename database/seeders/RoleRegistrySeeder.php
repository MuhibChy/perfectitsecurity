<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Support\RoleRegistry;
use Illuminate\Database\Seeder;

/**
 * Syncs the central role registry from code definitions.
 * Safe to re-run: upserts by internal name, never deletes rows, so
 * deprecating a role in code never orphans historical users.
 */
class RoleRegistrySeeder extends Seeder
{
    public function run(): void
    {
        foreach (RoleRegistry::definitions() as $order => $def) {
            Role::updateOrCreate(
                ['name' => $def['name']],
                $def + ['sort_order' => $def['sort_order'] ?? ($order * 10)]
            );
        }
    }
}
