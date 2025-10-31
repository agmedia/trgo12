<?php
// ProductAttributeTranslation.php
namespace App\Models\Back\Catalog\Product;

use Illuminate\Database\Eloquent\Model;

class ProductAttributeTranslation extends Model
{

    public $timestamps = false;

    protected $guarded = [];

    protected $table = 'product_attribute_translations';
}
