<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles; we’ll attach permissions later
        foreach (['master','admin','manager','editor','customer'] as $r) {
            Role::findOrCreate($r);
        }
        
        // Reset cached roles/permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
