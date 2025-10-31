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


    /*******************************************************************************
    *                                Copyright : AGmedia                           *
    *                              email: filip@agmedia.hr                         *
    *******************************************************************************/

    // Tabovi / grupe u adminu
    'groups'      => [
        'site'    => ['label' => 'Stranica', 'icon' => 'ti ti-world', 'i18n' => false],
        'ui'      => ['label' => 'UI', 'icon' => 'ti ti-layout', 'i18n' => false],
        'company' => ['label' => 'Basic Info', 'icon' => 'ti ti-building', 'i18n' => false],
    ],

    /**
     *
     * // Anywhere (npr. u paginacijama)
     * $perPageAdmin = app(\App\Services\Settings\SettingsManager::class)->get('ui', 'admin_pagination', 20);
     *
     * // I18n string:
     * $title = app(\App\Services\Settings\SettingsManager::class)->get('site', 'title', ['hr'=>'','en'=>''])['hr'] ?? '';
     *
     */
    // Polja po grupi (tip: text|email|number|boolean|i18n_text|i18n_textarea|textarea|decimal)
    'fields'      => [
        'site' => [
            //
            'meta_title' => ['type' => 'i18n_text', 'label' => 'Meta naslov', 'default' => '', 'col' => 6],
            'meta_description' => ['type' => 'i18n_textarea', 'label' => 'Meta opis stranice', 'default' => '', 'col' => 12],
            'meta_keywords' => ['type' => 'i18n_text', 'label' => 'Meta ključne riječi', 'default' => '', 'col' => 12],
        ],

        'ui' => [
            'admin_pagination' => ['type' => 'number', 'label' => 'Admin paginacija', 'default' => 20, 'min' => 5, 'max' => 200, 'step' => 1, 'col' => 3],
            'front_pagination' => ['type' => 'number', 'label' => 'Front paginacija', 'default' => 12, 'min' => 6, 'max' => 60, 'step' => 1, 'col' => 3]
        ],

        'company' => [
            'send_admin_emails'       => ['type' => 'boolean', 'label' => 'Šalji email-ove administratoru?', 'default' => true],
        ],

    ],

];
