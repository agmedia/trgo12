<?php

namespace App\Http\Controllers\Back\Settings\Shop;

use App\Http\Controllers\Back\Settings\Base\AbstractSettingsListPageController;

class TaxPageController extends AbstractSettingsListPageController
{
    protected string $sectionCode = 'tax';
    protected string $view        = 'back.settings.shop.taxes';
}
