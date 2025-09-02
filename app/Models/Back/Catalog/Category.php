<?php

namespace App\Models\Back\Catalog;


use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;


class Category extends Model implements HasMedia
{
    use NodeTrait;
    use InteractsWithMedia;
    
    
    protected $fillable = [
        'group', 'is_active', 'is_navbar', 'is_footer', 'position', 'parent_id',
    ];
    
    
    protected $casts = [
        'is_active' => 'bool',
        'is_navbar' => 'bool',
        'is_footer' => 'bool',
        'position' => 'int',
    ];


// Relationships
    public function translations()
    {
        return $this->hasMany(CategoryTranslation::class);
    }


// Helper to get translation for a locale (falls back to app fallback)
    public function translation(?string $locale = null): ?CategoryTranslation
    {
        $locale = $locale ?: app()->getLocale();
        $fallback = config('app.fallback_locale');
        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', $fallback);
    }


// Scopes
    public function scopeForGroup($query, string $group)
    {
        return $query->where('group', $group)->defaultOrder();
    }


// Media collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
        $this->addMediaCollection('icon')->singleFile();
        $this->addMediaCollection('banner')->singleFile();
    }
}