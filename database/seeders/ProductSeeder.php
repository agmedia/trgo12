<?php

namespace Database\Seeders;

use App\Models\Back\Catalog\Category;
use App\Models\Back\Catalog\Product\Product;
use App\Models\Back\Catalog\Product\ProductImage;
use App\Models\Back\Catalog\Product\ProductImageTranslation;
use App\Models\Back\Catalog\Product\ProductTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Allow: php artisan db:seed --class=ProductSeeder -- --count=123
        $count = 50;

        $locales = array_keys(config('app.locales', ['en' => 'English']));
        $defaultImagePath = 'media/avatars/default_image.jpg'; // stored path
        $categoryIds = Category::query()->pluck('id')->all();

        for ($i = 1; $i <= $count; $i++) {
            $titleBase = 'Demo product '.$i;

            $product = Product::create([
                // manufacturers for now: null (FK-safe). If you insist on 0, remove the FK in migration.
                'manufacturer_id'    => rand(1, 5),
                'sku'                => 'SKU-'.str_pad((string)$i, 6, '0', STR_PAD_LEFT),
                'ean'                => null,
                'isbn'               => null,
                'price'              => fake()->randomFloat(2, 5, 999), // respects decimal:2
                'quantity'           => fake()->numberBetween(0, 250),
                'track_stock'        => true,
                'decrease_on_purchase'=> true,
                'tax_id'             => 1,
                'viewed'             => 0,
                'sort_order'         => $i,
                'featured'           => fake()->boolean(10),
                'status'             => true,
                'published_at'       => now(),
            ]);

            // Categories (attach 1–3 random existing if available)
            if (!empty($categoryIds)) {
                $attach = collect($categoryIds)->shuffle()->take(rand(1, min(3, count($categoryIds))))->all();
                $product->categories()->syncWithoutDetaching($attach);
            }

            // Translations per locale
            foreach ($locales as $code) {
                $title = $titleBase.' ('.$code.')';
                ProductTranslation::create([
                    'product_id'  => $product->id,
                    'locale'      => $code,
                    'title'       => $title,
                    'slug'        => Str::slug($title).'-'.$product->id,
                    'description' => 'Seeded demo description for '.$titleBase.'.',
                    'meta_title'  => $title,
                    'meta_description' => 'Meta for '.$titleBase,
                ]);
            }

            // Default image
            $image = ProductImage::create([
                'product_id'   => $product->id,
                'path'         => $defaultImagePath,
                'is_default'   => true,
                'is_published' => true,
                'sort_order'   => 0,
            ]);

            // Image translations
            foreach ($locales as $code) {
                ProductImageTranslation::create([
                    'image_id' => $image->id,
                    'locale'   => $code,
                    'title'    => 'Default image',
                    'alt_text' => 'Default image',
                ]);
            }
        }

        $this->command->info("Seeded {$count} products with default images.");
    }
}
