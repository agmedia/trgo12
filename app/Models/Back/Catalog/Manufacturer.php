<?php
// app/Models/Back/Catalog/Manufacturer.php
namespace App\Models\Back\Catalog;

use App\Models\Back\Catalog\Product\Product;
use Illuminate\Database\Eloquent\Model;

class Manufacturer extends Model
{

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'status'       => 'boolean',
        'featured'     => 'boolean',
        'published_at' => 'datetime',
    ];


    public function translations()
    {
        return $this->hasMany(ManufacturerTranslation::class);
    }


    public function translation(?string $locale = null): ?ManufacturerTranslation
    {
        $locale   = $locale ?: app()->getLocale();
        $fallback = config('app.fallback_locale');

        return $this->translations->firstWhere('locale', $locale)
               ?? $this->translations->firstWhere('locale', $fallback);
    }


    public function products()
    {
        return $this->hasMany(Product::class);
    }


    public function categories()
    {
        return $this->belongsToMany(Category::class, 'manufacturer_category');
    }
}
