<?php
// ProductAttribute.php
namespace App\Models\Back\Catalog\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductAttribute extends Model
{

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = ['status' => 'boolean', 'is_filterable' => 'boolean', 'is_visible' => 'boolean'];


    public function translations(): HasMany
    {
        return $this->hasMany(ProductAttributeTranslation::class, 'attribute_id');
    }


    public function translation(?string $locale = null): ?ProductAttributeTranslation
    {
        $locale   = $locale ?: app()->getLocale();
        $fallback = config('app.fallback_locale');

        return $this->translations->firstWhere('locale', $locale)
               ?? $this->translations->firstWhere('locale', $fallback);
    }


    public function attributeValues()
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id');
    }
}
