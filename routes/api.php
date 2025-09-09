<?php

use App\Http\Controllers\Api\V1\Settings\CurrencyController as ApiCurrencyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:60,1', 'auth:sanctum'])->prefix('v1')->group(function () {
    Route::prefix('settings/currencies')->name('api.v1.settings.currencies.')->group(function () {
        Route::get('/',      [ApiCurrencyController::class, 'index'])->name('index');
        Route::post('/',     [ApiCurrencyController::class, 'store'])->name('store');
        Route::post('/main', [ApiCurrencyController::class, 'storeMain'])->name('storeMain');
        Route::delete('/',   [ApiCurrencyController::class, 'destroy'])->name('destroy');
    });
});