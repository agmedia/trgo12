<?php

namespace App\Shipping\Providers\Pickup;

use App\Shipping\Contracts\ShippingProviderInterface;

class Driver implements ShippingProviderInterface
{
    public static function code(): string
    {
        return 'pickup';
    }

    public static function defaultTitle(): array
    {
        return ['hr' => 'Osobno preuzimanje', 'en' => 'Local Pickup'];
    }

    public static function defaultConfig(): array
    {
        return [
            'min'               => null,              // minimum order amount to show method (nullable)
            'short_description' => ['hr' => '', 'en' => ''],
            'instructions'      => ['hr' => '', 'en' => ''], // shown on order/checkout (optional)
            'location'          => '',               // pickup location text
        ];
    }

    public static function validationRules(): array
    {
        return [
            'config.min'                 => ['nullable', 'numeric', 'min:0'],
            'config.short_description'   => ['nullable', 'array'],
            'config.instructions'        => ['nullable', 'array'],
            'config.location'            => ['nullable', 'string', 'max:255'],
            'geo_zone'                   => ['nullable', 'integer'],
        ];
    }

    public static function backModalBlade(): string
    {
        return 'back.settings.shop.shipping.modals.pickup';
    }
}
