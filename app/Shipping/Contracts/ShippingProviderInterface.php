<?php

namespace App\Shipping\Contracts;

interface ShippingProviderInterface
{
    public static function code(): string;
    /** e.g. ['hr'=>'Preuzimanje', 'en'=>'Pickup'] */
    public static function defaultTitle(): array;
    /** provider-specific defaults (may include nested arrays / per-locale maps) */
    public static function defaultConfig(): array;
    /** Laravel validation rules for `data.config` and optional fields */
    public static function validationRules(): array;
    /** back-office modal blade path for this provider */
    public static function backModalBlade(): string;
}
