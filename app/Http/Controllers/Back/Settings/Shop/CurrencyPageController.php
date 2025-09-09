<?php

namespace App\Http\Controllers\Back\Settings\Shop;

use App\Http\Controllers\Back\Settings\Base\AbstractSettingsListPageController;
use Illuminate\Support\Collection;

class CurrencyPageController extends AbstractSettingsListPageController
{
    protected string $sectionCode = 'currency';
    protected string $view        = 'back.settings.shop.currency';

    protected function extras(Collection $items): array
    {
        $main = $items->firstWhere('main', true);
        return ['main' => $main];
    }
}
