<?php
// ProductAttributeValueTranslation.php
namespace App\Models\Back\Catalog\Product;

use Illuminate\Database\Eloquent\Model;

class ProductAttributeValueTranslation extends Model
{

    public $timestamps = false;

    protected $guarded = [];

    protected $table = 'product_attribute_value_translations';
}
