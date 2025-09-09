<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Back\User\UserDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Ensure roles exist (so assignRole/syncRoles won't blow up)
    foreach (['master', 'admin', 'manager', 'editor', 'customer'] as $r) {
        Role::findOrCreate($r);
    }

    Storage::fake('public');

    // Act as a master (safe if you later add `role:*` middleware)
    $admin = User::factory()->create(['email' => 'admin@test.local']);
    $admin->assignRole('master');
    $this->actingAs($admin);
});

it('creates a customer with avatar', function () {
    $payload = [
        'role'        => 'customer',
        'status'      => 1,
        'fname'       => 'Ana',
        'lname'       => 'Kovač',
        'email'       => 'ana@example.com',
        'password'    => 'secret123',
        'avatar_file' => UploadedFile::fake()->image('a.jpg', 300, 300),
    ];

    $resp = $this->post(route('users.store'), $payload);
    $resp->assertRedirect(route('users.index', ['role' => 'customer']));

    $detail = UserDetail::first();
    expect($detail)->not->toBeNull();
    expect($detail->role)->toBe('customer');
    expect($detail->user->email)->toBe('ana@example.com');

    // Spatie role assigned
    expect($detail->user->hasRole('customer'))->toBeTrue();

    // Avatar physically stored on fake disk
    expect($detail->avatar)->toStartWith('storage/');
    $rel = str($detail->avatar)->after('storage/')->value();
    expect(Storage::disk('public')->exists($rel))->toBeTrue();
});

it('updates a manager, clears avatar and changes email', function () {
    // Seed one
    $u = User::factory()->create(['email' => 'old@ex.com']);
    $u->assignRole('manager');

    $detail = UserDetail::create([
        'user_id' => $u->id,
        'role'    => 'manager',
        'status'  => 1,
        'fname'   => 'Marko',
        'lname'   => 'Ivić',
        'avatar'  => 'images/avatars/default_avatar.jpg',
    ]);

    $resp = $this->put(route('users.update', $detail), [
        'role'          => 'manager',
        'status'        => 0,
        'fname'         => 'Marko',
        'lname'         => 'Ivić',
        'email'         => 'new@ex.com',
        'password'      => '',           // keep existing
        'remove_avatar' => 1,
    ]);
    $resp->assertRedirect(route('users.index', ['role' => 'manager']));

    $detail->refresh();
    expect($detail->status)->toBeFalse();
    expect($detail->user->email)->toBe('new@ex.com');
    expect($detail->avatar)->toBe('images/avatars/default_avatar.jpg');

    // Spatie role preserved
    expect($detail->user->hasRole('manager'))->toBeTrue();
});

it('lists only the selected role on index (filter by ?role=customer)', function () {
    // One manager, one customer
    $um = User::factory()->create(['email' => 'm@ex.com']);
    $um->assignRole('manager');
    UserDetail::create(['user_id' => $um->id, 'role' => 'manager', 'status' => 1, 'fname' => 'Mara', 'lname' => 'Mgr']);

    $uc = User::factory()->create(['email' => 'c@ex.com']);
    $uc->assignRole('customer');
    UserDetail::create(['user_id' => $uc->id, 'role' => 'customer', 'status' => 1, 'fname' => 'Ciro', 'lname' => 'Cust']);

    $res = $this->get(route('users.index', ['role' => 'customer']));
    $res->assertOk();
    $res->assertSee('c@ex.com');
    $res->assertDontSee('m@ex.com'); // filtered out
});

it('shows the edit form', function () {
    $u = User::factory()->create(['email' => 'edit@ex.com']);
    $u->assignRole('editor');
    $detail = UserDetail::create([
        'user_id' => $u->id, 'role' => 'editor', 'status' => 1,
        'fname'   => 'Edita', 'lname' => 'Reds',
    ]);

    $res = $this->get(route('users.edit', $detail));
    $res->assertOk();
    $res->assertSee('Edita');       // form filled
    $res->assertSee('edit@ex.com'); // base User email shown
});
