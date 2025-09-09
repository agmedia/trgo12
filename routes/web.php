<?php

use App\Http\Controllers\Back\Catalog\CategoryController;
use App\Http\Controllers\Back\Catalog\ManufacturerController;
use App\Http\Controllers\Back\Catalog\ProductController;
use App\Http\Controllers\Back\DashboardController;
use App\Http\Controllers\Back\LocaleController;
use App\Http\Controllers\Back\Settings\Shop\CurrencyPageController;
use App\Http\Controllers\Api\V1\Settings\CurrencyController as ApiCurrencyController;
use App\Http\Controllers\Back\Settings\Shop\GeozonePageController;
use App\Http\Controllers\Api\V1\Settings\GeozoneController as ApiGeozoneController;
use App\Http\Controllers\Back\Settings\Shop\LanguagePageController;
use App\Http\Controllers\Api\V1\Settings\LanguageController as ApiLanguageController;
use App\Http\Controllers\Back\Settings\Shop\PaymentsPageController;
use App\Http\Controllers\Api\V1\Settings\PaymentsController as ApiPaymentsController;
use App\Http\Controllers\Back\Settings\Shop\ShippingPageController;
use App\Http\Controllers\Api\V1\Settings\ShippingController as ApiShippingController;
use App\Http\Controllers\Back\Settings\Shop\TaxPageController;
use App\Http\Controllers\Api\V1\Settings\TaxController as ApiTaxController;
use App\Http\Controllers\Back\Settings\Shop\OrderStatusPageController;
use App\Http\Controllers\Api\V1\Settings\OrderStatusController as ApiOrderStatusController;
use App\Http\Controllers\Back\User\PasswordController;
use App\Http\Controllers\Back\User\UsersController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
     ->middleware('auth')
     ->name('dashboard');


Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', fn () => redirect()->route('dashboard'))->name('admin.home');
    Route::post('/locale', LocaleController::class)->name('locale.switch');

    Route::prefix('catalog')->as('catalog.')->group(function () {
        Route::resource('categories', CategoryController::class)->names('categories');
        Route::resource('products', ProductController::class)->names('products');
        Route::resource('manufacturers', ManufacturerController::class)->names('manufacturers');
    });
    
    Route::resource('users', UsersController::class)->names('users');

    Route::get('profile', [UsersController::class, 'profile'])->name('users.profile');
    Route::put('profile', [UsersController::class, 'profileUpdate'])->name('users.profile.update');

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('currencies', [CurrencyPageController::class, 'index'])->name('currencies.index');
        Route::get('languages', [LanguagePageController::class, 'index'])->name('languages.index');
        Route::get('taxes', [TaxPageController::class, 'index'])->name('taxes.index');
        Route::get('statuses', [OrderStatusPageController::class, 'index'])->name('statuses.index');

        Route::get('payments', [PaymentsPageController::class, 'index'])->name('payments.index');
        Route::get('shipping', [ShippingPageController::class, 'index'])->name('shipping.index');

        Route::get('geozones',            [GeozonePageController::class, 'index'])->name('geozones.index');
        Route::get('geozones/edit/{id?}', [GeozonePageController::class, 'edit'])->name('geozones.edit');

    });

    // Keep the old URLs & names for tests / templates that still use them
    // Profile (mapped to UsersController)
    Route::get('/settings/profile', [UsersController::class, 'profile'])->name('settings.profile');
    Route::put('/settings/profile', [UsersController::class, 'profileUpdate'])->name('settings.profile.update');
    Route::patch('/settings/profile', [UsersController::class, 'profileUpdate']);

    // Password (new tiny controller below)
    Route::get('/settings/password', [PasswordController::class, 'edit'])->name('settings.password');
    Route::put('/settings/password', [PasswordController::class, 'update'])->name('settings.password.update');
});


Route::middleware(['web', 'auth'])->prefix('api/v1/settings')->name('api.v1.settings.')->group(function () {
    Route::post('currencies',       [ApiCurrencyController::class, 'store'])->name('currencies.store');
    Route::post('currencies/main',  [ApiCurrencyController::class, 'storeMain'])->name('currencies.storeMain');
    Route::delete('currencies',     [ApiCurrencyController::class, 'destroy'])->name('currencies.destroy');

    Route::post('languages',   [ApiLanguageController::class, 'store'])->name('languages.store');
    Route::delete('languages', [ApiLanguageController::class, 'destroy'])->name('languages.destroy');

    Route::post('taxes',   [ApiTaxController::class, 'store'])->name('taxes.store');
    Route::delete('taxes', [ApiTaxController::class, 'destroy'])->name('taxes.destroy');

    Route::post('statuses',   [ApiOrderStatusController::class, 'store'])->name('statuses.store');
    Route::delete('statuses', [ApiOrderStatusController::class, 'destroy'])->name('statuses.destroy');

    Route::post('payments',   [ApiPaymentsController::class, 'store'])->name('payments.store');
    Route::delete('payments', [ApiPaymentsController::class, 'destroy'])->name('payments.destroy');

    Route::post('shipping',   [ApiShippingController::class, 'store'])->name('shipping.store');
    Route::delete('shipping', [ApiShippingController::class, 'destroy'])->name('shipping.destroy');

    Route::post('geozones',   [ApiGeozoneController::class, 'store'])->name('geozones.store');
    Route::delete('geozones', [ApiGeozoneController::class, 'destroy'])->name('geozones.destroy');
});

require __DIR__.'/auth.php';
