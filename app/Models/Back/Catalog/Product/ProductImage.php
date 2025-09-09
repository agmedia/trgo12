<?php

namespace App\Models\Back\Catalog\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductImage extends Model
{

    protected $guarded = ['id', 'created_at', 'updated_at'];


    public function translations(): HasMany
    {
        return $this->hasMany(ProductImageTranslation::class, 'image_id');
    }
}