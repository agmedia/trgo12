<?php

namespace App\Http\Controllers\Back\Settings\Shop;

use App\Http\Controllers\Back\Settings\Base\AbstractSettingsListPageController;
use Illuminate\Support\Collection;

class LanguagePageController extends AbstractSettingsListPageController
{
    protected string $sectionCode = 'language';
    protected string $view        = 'back.settings.shop.languages';

    protected function extras(Collection $items): array
    {
        $main = $items->firstWhere('main', true);
        return ['main' => $main];
    }
}
