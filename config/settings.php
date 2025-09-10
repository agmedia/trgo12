<?php

return [

    // ... your other settings ...
    'product_options_enabled' => true,
    //
    'product_attributes_enabled' => true,

    /**
     *
     */
    'payments' => [
        'providers' => [
            'cod' => [
                'name'    => 'Cash on Delivery',
                'enabled' => true,
                'driver'  => \App\Payments\Providers\Cod\Driver::class,
            ],
            'bank' => [
                'name'    => 'Bank Transfer',
                'enabled' => true,
                'driver'  => \App\Payments\Providers\Bank\Driver::class,
            ],
            'wspay' => [
                'name'    => 'WSPay',
                'enabled' => true,
                'driver'  => \App\Payments\Providers\Wspay\Driver::class,
            ],
            // add more ...
        ],
    ],

    'shipping' => [
        'providers' => [
            'pickup' => [
                'name'    => 'Local Pickup',
                'enabled' => true,
                'driver'  => \App\Shipping\Providers\Pickup\Driver::class,
            ],
            'flat' => [
                'name'    => 'Flat Rate',
                'enabled' => true,
                'driver'  => \App\Shipping\Providers\Flat\Driver::class,
            ],
        ],
    ],

];
