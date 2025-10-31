<?php
// ProductAttributeValue.php
namespace App\Models\Back\Catalog\Product;

use Illuminate\Database\Eloquent\Model;

class ProductAttributeValue extends Model
{

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = ['status' => 'boolean'];


    public function attribute()
    {
        return $this->belongsTo(ProductAttribute::class, 'attribute_id');
    }


    public function translations()
    {
        return $this->hasMany(ProductAttributeValueTranslation::class, 'value_id');
    }


    public function translation($locale = null)
    {
        $locale ??= app()->getLocale();

        return $this->translations()->where('locale', $locale)->first();
    }
}
