<?php

use App\Models\Back\Catalog\Product\Product;
use App\Models\Back\Settings\Settings;
use Illuminate\Support\Facades\Cache;

if (!function_exists('lang_list')) {
    /**
     * Return list of active languages (from settings.languages.list).
     */
    function lang_list(): \Illuminate\Support\Collection
    {
        return Cache::rememberForever('lang_list', function () {
            return Settings::get('languages', 'list')->where('status', true);
        });
    }
}

if (!function_exists('lang_main')) {
    /**
     * Return the main/default language.
     */
    function lang_main()
    {
        return Cache::rememberForever('lang_main', function () {
            return lang_list()->where('main', true)->first();
        });
    }
}

if (!function_exists('currency_list')) {
    /**
     * Return list of active currencies (from settings.currency.list).
     */
    function currency_list(): \Illuminate\Support\Collection
    {
        return Cache::rememberForever('currency_list', function () {
            return Settings::get('currency', 'list')->where('status', true);
        });
    }
}

if (!function_exists('currency_main')) {
    /**
     * Return the main/default currency.
     */
    function currency_main()
    {
        return Cache::rememberForever('currency_main', function () {
            return currency_list()->where('main', true)->first();
        });
    }
}

if ( ! function_exists('current_locale')) {
    /**
     * Retrieve the current application locale from the session or fallback to the default locale.
     */
    function current_locale(): string
    {
        return session('locale', 'hr');
    }
}

if ( ! function_exists('priceForVariant')) {
    /**
     * Calculate the price for a specific product variant.
     *
     * @param Product $product The product instance.
     * @param object  $pivot   The pivot record containing variant-specific data.
     *
     * @return float The calculated price for the product variant.
     */
    function priceForVariant(Product $product, $pivot)
    {
        // $pivot je pivot record (npr. iz $product->optionValues->first()->pivot)
        if (!is_null($pivot->price_override)) {
            return $pivot->price_override;
        }
        return (float) $product->price + (float) $pivot->price_delta;
    }
}