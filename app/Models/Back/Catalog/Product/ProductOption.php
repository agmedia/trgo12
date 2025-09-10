<?php

namespace App\Models\Back\Catalog\Product;

use Illuminate\Database\Eloquent\Model;

class ProductOption extends Model
{

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = ['status' => 'boolean'];


    public function translations()
    {
        return $this->hasMany(ProductOptionTranslation::class, 'option_id');
    }


    public function translation(?string $locale = null): ?ProductOptionTranslation
    {
        $locale   = $locale ?: app()->getLocale();
        $fallback = config('app.fallback_locale');

        return $this->translations->firstWhere('locale', $locale)
               ?? $this->translations->firstWhere('locale', $fallback);
    }


    public function optionValues()
    {
        return $this->hasMany(
            ProductOptionValue::class,
            'option_id'
        );
    }
}
