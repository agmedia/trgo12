<?php

namespace App\Models\Back\Catalog\Product;

use Illuminate\Database\Eloquent\Model;

class ProductTranslation extends Model
{

    protected $table = 'product_translations';

    public $timestamps = false;

    protected $guarded = [];
}