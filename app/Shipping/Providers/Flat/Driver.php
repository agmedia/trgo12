<?php

namespace App\Shipping\Providers\Flat;

use App\Shipping\Contracts\ShippingProviderInterface;

class Driver implements ShippingProviderInterface
{
    public static function code(): string
    {
        return 'flat';
    }

    public static function defaultTitle(): array
    {
        return ['hr' => 'Dostava (paušal)', 'en' => 'Flat Rate Shipping'];
    }

    public static function defaultConfig(): array
    {
        return [
            'price'             => 0,                 // flat fee
            'free_over'         => null,              // free if order total >= this
            'min'               => null,              // minimum order to show method
            'short_description' => ['hr' => '', 'en' => ''],
        ];
    }

    public static function validationRules(): array
    {
        return [
            'config.price'             => ['required', 'numeric', 'min:0'],
            'config.free_over'         => ['nullable', 'numeric', 'min:0'],
            'config.min'               => ['nullable', 'numeric', 'min:0'],
            'config.short_description' => ['nullable', 'array'],
            'geo_zone'                 => ['nullable', 'integer'],
        ];
    }

    public static function backModalBlade(): string
    {
        return 'back.settings.shop.shipping.modals.flat';
    }
}
