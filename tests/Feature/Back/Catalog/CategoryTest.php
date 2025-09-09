<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Back\Catalog\Category;
use App\Models\Back\Catalog\CategoryTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * Common bootstrapping for these tests:
 * - fake the public disk (Spatie stores media here by default)
 * - set locale to 'en' (controller writes translations for current locale)
 * - login as a user (admin middleware)
 */
beforeEach(function () {
    Storage::fake('public');
    app()->setLocale('en');
    
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $this->actingAs($user);
});

it('creates a category with image upload', function () {
    $payload = [
        'group'        => 'products',
        'parent_id'    => null,
        'position'     => 10,
        'is_active'    => 1,
        
        'title'            => 'Test Category',
        'slug'             => '', // let controller generate or keep null
        'description'      => 'Some description',
        'seo_title'        => 'SEO Title',
        'seo_description'  => 'SEO Description',
        'seo_keywords'     => 'test,cat',
        
        'image_file'  => UploadedFile::fake()->image('cat.jpg', 800, 600),
        // You could also test icon/banner the same way:
        // 'icon_file'   => UploadedFile::fake()->image('icon.png', 128, 128),
        // 'banner_file' => UploadedFile::fake()->image('banner.jpg', 1200, 360),
    ];
    
    $response = $this->post(route('catalog.categories.store'), $payload);
    $response->assertRedirect(route('catalog.categories.index', ['group' => 'products']));
    
    // assert DB rows
    $category = Category::query()->first();
    expect($category)->not->toBeNull();
    expect($category->group)->toBe('products');
    
    $translation = CategoryTranslation::query()
        ->where('category_id', $category->id)
        ->where('locale', 'en')
        ->first();
    
    expect($translation)->not->toBeNull();
    expect($translation->title)->toBe('Test Category');
    
    // assert media attached
    $media = $category->getFirstMedia('image');
    expect($media)->not->toBeNull();
    
    // File physically exists on the faked disk
    // (Spatie stores under storage/shop/public, which maps to the 'public' disk)
    $relativePath = str($media->getPath())->after(Storage::disk('public')->path(''))->value(); // safety for both abs/rel
    expect(Storage::disk('public')->exists($relativePath))->toBeTrue();
});

it('updates a category, removes old image and uploads a new banner', function () {
    // Arrange: existing category with EN translation and an image
    $category = Category::create([
        'group'     => 'products',
        'parent_id' => null,
        'position'  => 0,
        'is_active' => true,
    ]);
    
    CategoryTranslation::create([
        'category_id'     => $category->id,
        'locale'          => 'en',
        'title'           => 'Old Title',
        'slug'            => 'old-title',
        'description'     => 'Old desc',
        'seo_title'       => 'Old SEO',
        'seo_description' => 'Old SEO D',
        'seo_keywords'    => 'old,seo',
    ]);
    
    // pre-attach an image to test removal
    $category->addMediaFromString(UploadedFile::fake()->image('old.jpg')->get())
        ->usingFileName('old.jpg')
        ->toMediaCollection('image');
    
    // Act: update with new title, remove image, and upload a banner
    $payload = [
        'group'        => 'products',
        'parent_id'    => null,
        'position'     => 5,
        'is_active'    => 1,
        
        'title'            => 'New Title',
        'slug'             => 'new-title',
        'description'      => 'New desc',
        'seo_title'        => 'New SEO',
        'seo_description'  => 'New SEO D',
        'seo_keywords'     => 'new,seo',
        
        'remove_image' => 1,
        'banner_file'  => UploadedFile::fake()->image('banner.jpg', 1200, 360),
    ];
    
    $response = $this->put(route('catalog.categories.update', $category), $payload);
    $response->assertRedirect(route('catalog.categories.index', ['group' => 'products']));
    
    // Assert field changes
    $category->refresh();
    expect($category->position)->toBe(5);
    
    $translation = $category->translations()->where('locale', 'en')->first();
    expect($translation->title)->toBe('New Title');
    expect($translation->slug)->toBe('new-title');
    
    // Assert media mutations
    expect($category->getFirstMedia('image'))->toBeNull();           // removed
    expect($category->getFirstMedia('banner'))->not->toBeNull();     // uploaded
    
    $banner = $category->getFirstMedia('banner');
    $relativePath = str($banner->getPath())->after(Storage::disk('public')->path(''))->value();
    expect(Storage::disk('public')->exists($relativePath))->toBeTrue();
});
