<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Back\User\UserDetail;
use App\Models\Back\Catalog\Category;
use App\Models\Back\Catalog\CategoryTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['master', 'admin', 'manager', 'editor', 'customer'] as $r) {
        Role::findOrCreate($r);
    }
    $admin = User::factory()->create(['email' => 'smoke@test.local']);
    $admin->assignRole('master');
    $this->actingAs($admin);

    // Minimal fixtures for routes that need a model
    $cat = Category::create(['group' => 'products', 'is_active' => true]);
    CategoryTranslation::create([
        'category_id' => $cat->id, 'locale' => 'en', 'title' => 'SmokeCat', 'slug' => 'smokecat'
    ]);

    $usr = User::factory()->create(['email' => 'edit-smoke@ex.com']);
    $usr->assignRole('customer');
    UserDetail::create(['user_id' => $usr->id, 'role' => 'customer', 'status' => 1, 'fname' => 'Smoke', 'lname' => 'User']);
});

it('loads key admin routes without errors', function () {
    $routes = [
        fn() => $this->get(route('dashboard')),
        fn() => $this->get(route('catalog.categories.index', ['group' => 'products'])),
        fn() => $this->get(route('catalog.categories.create', ['group' => 'products'])),
        fn() => $this->get(route('users.index', ['role' => 'customer'])),
        fn() => $this->get(route('users.create', ['role' => 'customer'])),
    ];

    foreach ($routes as $call) {
        $res = $call();
        // 200 (OK) or 302 (auth redirects inside, etc.) are acceptable in a smoke pass
        expect(in_array($res->getStatusCode(), [200, 302], true))->toBeTrue();
    }
});
