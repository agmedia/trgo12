<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Back\Catalog\Product\ProductAttribute;
use App\Models\Back\Catalog\Product\ProductAttributeTranslation;
use App\Models\Back\Catalog\Product\ProductAttributeValue;
use App\Models\Back\Catalog\Product\ProductAttributeValueTranslation;
use App\Models\Back\Catalog\Product\Product;

class ProductAttributeSeeder extends Seeder
{
    public function run(): void
    {
        // php artisan db:seed --class=ProductAttributeSeeder -- --attach
        $attach = true;//(bool) ($this->command->option('attach') ?? false);
        $locales = array_keys(config('app.locales', ['en'=>'English']));

        $createAttr = function (array $titles, int $sort) use ($locales) {
            $attr = ProductAttribute::create(['status'=>true,'is_filterable'=>true,'is_visible'=>true,'sort_order'=>$sort]);
            foreach ($locales as $loc) {
                $t = $titles[$loc] ?? $titles['en'] ?? 'Attribute';
                ProductAttributeTranslation::create([
                    'attribute_id'=>$attr->id,
                    'locale'=>$loc,
                    'title'=>$t,
                    'slug'=>Str::slug($t).'-'.$attr->id,
                ]);
            }
            return $attr;
        };

        $createVal = function (ProductAttribute $attr, array $titles, int $sort) use ($locales) {
            $val = ProductAttributeValue::create(['attribute_id'=>$attr->id,'status'=>true,'sort_order'=>$sort]);
            foreach ($locales as $loc) {
                $t = $titles[$loc] ?? $titles['en'] ?? 'Value';
                ProductAttributeValueTranslation::create([
                    'value_id'=>$val->id,
                    'locale'=>$loc,
                    'title'=>$t,
                ]);
            }
            return $val;
        };

        // Materijal / Material
        $attrMaterial = $createAttr(['en'=>'Material','hr'=>'Materijal'], 1);
        $cotton = $createVal($attrMaterial, ['en'=>'Cotton','hr'=>'Pamuk'], 1);
        $wool   = $createVal($attrMaterial, ['en'=>'Wool','hr'=>'Vuna'], 2);
        $poly   = $createVal($attrMaterial, ['en'=>'Polyester','hr'=>'Poliester'], 3);

        // Spol / Gender
        $attrGender = $createAttr(['en'=>'Gender','hr'=>'Spol'], 2);
        $male   = $createVal($attrGender, ['en'=>'Male','hr'=>'Muško'], 1);
        $female = $createVal($attrGender, ['en'=>'Female','hr'=>'Žensko'], 2);
        $unisex = $createVal($attrGender, ['en'=>'Unisex','hr'=>'Unisex'], 3);

        if ($attach && config('settings.product_attributes_enabled')) {
            $products = Product::query()->get();
            foreach ($products as $p) {
                $attachIds = [
                    $cotton->id,
                    [ $male->id, $female->id, $unisex->id ][array_rand([0,1,2])]
                ];
                $p->attributeValues()->syncWithoutDetaching(array_unique($attachIds));
            }
            $this->command->info('Attached sample attributes to products.');
        }

        $this->command->info('Seeded product attributes (Material, Gender) with values & translations.');
    }
}
