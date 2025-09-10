<?php

namespace App\Models\Back\Catalog\Product;

use Illuminate\Database\Eloquent\Model;

class ProductOptionValue extends Model
{

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = ['status' => 'boolean'];


    public function option()
    {
        return $this->belongsTo(ProductOption::class, 'option_id');
    }


    public function translations()
    {
        return $this->hasMany(ProductOptionValueTranslation::class, 'value_id');
    }


    public function translation($lang = null)
    {
        $lang ??= app()->getLocale();

        return $this->translations()->where('locale', $lang)->first();
    }


    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'product_option_value_product',
            'option_value_id',
            'product_id'
        )->withPivot(['product_image_id','sku_full','sku_suffix','quantity','price_delta','price_override','is_default','extra']);
    }
}
