<?php

namespace App\Http\Controllers\Back\Settings\Shop;

use App\Http\Controllers\Back\Settings\Base\AbstractProvidersPageController;

class PaymentsPageController extends AbstractProvidersPageController
{
    protected string $sectionCode         = 'payments';
    protected string $view                = 'back.settings.shop.payments';
    protected string $configProvidersPath = 'settings.payments.providers';
    protected bool   $needsGeoZones       = true; // COD/Bank/WSPay etc. may need it
}
