<?php

namespace App\Http\Controllers\Back\Settings\Shop;

use App\Http\Controllers\Back\Settings\Base\AbstractProvidersPageController;

class ShippingPageController extends AbstractProvidersPageController
{
    protected string $sectionCode         = 'shipping';
    protected string $view                = 'back.settings.shop.shipping';
    protected string $configProvidersPath = 'settings.shipping.providers';
    protected bool   $needsGeoZones       = true; // flat/pickup may filter by geozone
}
