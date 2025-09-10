<?php

namespace App\Models\Back\Catalog\Product;

use App\Models\Back\Catalog\Manufacturer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, BelongsToMany};
use App\Models\Back\Catalog\Category;

class Product extends Model
{

    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'status' => 'boolean',
        'price'  => 'decimal:2',
    ];


    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }


    public function translation(?string $locale = null): ?ProductTranslation
    {
        $locale   = $locale ?: app()->getLocale();
        $fallback = config('app.fallback_locale');

        return $this->translations->firstWhere('locale', $locale)
               ?? $this->translations->firstWhere('locale', $fallback);
    }


    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }


    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_category');
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }


    public function optionValues()
    {
        return $this->belongsToMany(
            ProductOptionValue::class,
            'product_option_value_product',
            'product_id',
            'option_value_id'
        )->withPivot([
            'product_image_id',
            'sku_full', 'sku_suffix',
            'quantity',
            'price_delta', 'price_override',
            'is_default', 'extra',
        ])->withTimestamps(); // ako dodaš timestamps na pivot
    }


}