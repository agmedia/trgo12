<?php

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