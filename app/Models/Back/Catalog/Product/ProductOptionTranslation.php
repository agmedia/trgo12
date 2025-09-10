<?php

namespace App\Models\Back\Catalog\Product;

use Illuminate\Database\Eloquent\Model;

class ProductOptionTranslation extends Model
{

    public $timestamps = false;

    protected $guarded = [];

    protected $table = 'product_option_translations';
}
