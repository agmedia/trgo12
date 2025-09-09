<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command?->newLine();
        $this->command?->info('=== Starting database seed ===');
        
        $this->call([
            PermissionSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            SettingsSeeder::class,
            ManufacturerSeeder::class,
            ProductSeeder::class,
        ]);
        
        $this->command?->info('=== Database seed finished ===');
        $this->command?->newLine();
    }
}
