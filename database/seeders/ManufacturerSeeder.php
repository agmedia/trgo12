<?php

namespace Database\Seeders;

use App\Models\Back\Catalog\Product\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Back\Catalog\Manufacturer;
use App\Models\Back\Catalog\ManufacturerTranslation;

class ManufacturerSeeder extends Seeder
{

    public function run(): void
    {
        // php artisan db:seed --class=ManufacturerSeeder -- --count=20 --attach
        /*$count  = (int) ($this->command->option('count') ?? 50);
        $attach = (bool) ($this->command->option('attach') ?? false);*/
        $count  = 10;
        $attach = false;

        $locales = array_keys(config('app.locales', ['en' => 'English']));

        // Seed N manufacturers
        for ($i = 1; $i <= $count; $i++) {
            $base = "Brand $i";

            $m = Manufacturer::create([
                'status'           => true,
                'featured'         => fake()->boolean(10),
                'sort_order'       => $i,
                'website_url'      => fake()->optional()->url(),
                'support_email'    => fake()->optional()->companyEmail(),
                'phone'            => fake()->optional()->phoneNumber(),
                'country_code'     => fake()->optional()->countryCode(), // ISO2
                'established_year' => fake()->optional()->numberBetween(1900, now()->year),
                'logo_path'        => 'media/brands/default_brand_logo.png',
                'published_at'     => now(),
            ]);

            foreach ($locales as $code) {
                $title = "$base ($code)";
                ManufacturerTranslation::create([
                    'manufacturer_id'  => $m->id,
                    'locale'           => $code,
                    'title'            => $title,
                    'slug'             => Str::slug($title) . '-' . $m->id,
                    'description'      => 'Seeded manufacturer description for ' . $base . '.',
                    'meta_title'       => $title,
                    'meta_description' => 'Meta for ' . $base,
                ]);
            }
        }

        // Optionally attach to existing products: assign a random manufacturer
        if ($attach) {
            $ids = Manufacturer::query()->pluck('id')->all();
            if ($ids) {
                Product::query()->inRandomOrder()->chunkById(500, function ($chunk) use ($ids) {
                    foreach ($chunk as $p) {
                        $p->update(['manufacturer_id' => $ids[array_rand($ids)]]);
                    }
                });
                $this->command->info('Attached manufacturers to existing products.');
            }
        }

        $this->command->info("Seeded {$count} manufacturers.");
    }
}
