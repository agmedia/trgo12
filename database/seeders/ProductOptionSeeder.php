<?php

namespace Database\Seeders;

use App\Models\Back\Catalog\Product\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Back\Catalog\Product\ProductOption;
use App\Models\Back\Catalog\Product\ProductOptionTranslation;
use App\Models\Back\Catalog\Product\ProductOptionValue;
use App\Models\Back\Catalog\Product\ProductOptionValueTranslation;
use App\Models\Back\Catalog\ProductImage;

class ProductOptionSeeder extends Seeder
{
    public function run(): void
    {
        // php artisan db:seed --class=ProductOptionSeeder -- --attach
        $attach = true;//(bool) ($this->command->option('attach') ?? false);
        $locales = array_keys(config('app.locales', ['en' => 'English']));

        // Helper to create option with translations
        $createOption = function (string $key, array $titles, int $sort) use ($locales) {
            $opt = ProductOption::create(['status' => true, 'sort_order' => $sort]);
            foreach ($locales as $locale) {
                $title = $titles[$locale] ?? ($titles['en'] ?? ucfirst($key));
                ProductOptionTranslation::create([
                    'option_id' => $opt->id,
                    'locale'    => $locale,
                    'title'     => $title,
                    'slug'      => Str::slug($title).'-'.$opt->id
                ]);
            }
            return $opt;
        };

        $createValue = function (ProductOption $opt, array $titles, int $sort) use ($locales) {
            $val = ProductOptionValue::create(['option_id' => $opt->id, 'status' => true, 'sort_order' => $sort]);
            foreach ($locales as $locale) {
                $title = $titles[$locale] ?? ($titles['en'] ?? 'Value');
                ProductOptionValueTranslation::create([
                    'value_id' => $val->id,
                    'locale'   => $locale,
                    'title'    => $title,
                ]);
            }
            return $val;
        };

        // Color / Boja
        $optColor = $createOption('color', [
            'en' => 'Color',
            'hr' => 'Boja',
        ], 1);

        $red  = $createValue($optColor, ['en' => 'Red',  'hr' => 'Crvena'], 1);
        $blue = $createValue($optColor, ['en' => 'Blue', 'hr' => 'Plava'],  2);
        $green= $createValue($optColor, ['en' => 'Green','hr' => 'Zelena'], 3);

        // Size / Veličina
        $optSize = $createOption('size', [
            'en' => 'Size',
            'hr' => 'Veličina',
        ], 2);

        $s = $createValue($optSize, ['en' => 'S', 'hr' => 'S'], 1);
        $m = $createValue($optSize, ['en' => 'M', 'hr' => 'M'], 2);
        $l = $createValue($optSize, ['en' => 'L', 'hr' => 'L'], 3);

        // Optional: attach some variants to products
        if ($attach && config('settings.product_options_enabled')) {
            $products = Product::query()->with('images')->get();

            foreach ($products as $p) {
                $imgId = optional($p->images->first())->id;

                // Attach a red variant and size M with some demo pivot data
                $attach = [
                    $red->id => [
                        'product_image_id' => $imgId,
                        'sku_suffix'       => '-RED',
                        'quantity'         => rand(0, 50),
                        'price_delta'      => 5.00,
                        'price_override'   => null,
                        'is_default'       => true,
                    ],
                    $m->id => [
                        'product_image_id' => $imgId,
                        'sku_suffix'       => '-M',
                        'quantity'         => rand(0, 50),
                        'price_delta'      => 0.00,
                        'price_override'   => null,
                        'is_default'       => false,
                    ],
                ];

                $p->optionValues()->syncWithoutDetaching($attach);
            }

            $this->command->info('Attached sample option values to products with pivot data.');
        }

        $this->command->info('Seeded product options (Color/Size) with translations and values.');
    }
}
