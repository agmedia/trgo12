<?php

namespace App\Http\Controllers\Back\Settings\Shop;

use App\Http\Controllers\Back\Settings\Base\AbstractSettingsListPageController;

class OrderStatusPageController extends AbstractSettingsListPageController
{
    protected string $sectionCode = 'order_statuses';
    protected string $view        = 'back.settings.shop.order-statuses';
}
