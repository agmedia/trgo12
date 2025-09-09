<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Groups
        $group_list = json_encode([
            (object) [
                'id'    => 'product',
                'title' => (object) ['hr' => 'Artikl', 'en' => 'Product'],
            ],
            (object) [
                'id'    => 'category',
                'title' => (object) ['hr' => 'Kategorija', 'en' => 'Category'],
            ],
            (object) [
                'id'    => 'manufacturer',
                'title' => (object) ['hr' => 'Proizvođač', 'en' => 'Manufacturer'],
            ],
        ], JSON_UNESCAPED_UNICODE);

// Types
        $type_list = json_encode([
            (object) [
                'id'    => 'P',
                'title' => (object) ['hr' => 'Postotak', 'en' => 'Percentage'],
            ],
            (object) [
                'id'    => 'F',
                'title' => (object) ['hr' => 'Fiksni', 'en' => 'Fixed'],
            ],
        ], JSON_UNESCAPED_UNICODE);

// Currencies
        $currency_list = json_encode([
            (object) [
                'id'             => 1,
                'title'          => (object) ['hr' => 'Euro', 'en' => 'Euro'],
                'code'           => 'EUR',
                'status'         => true,
                'main'           => true,
                'symbol_left'    => '€',
                'symbol_right'   => null,
                'value'          => '1.000000',
                'decimal_places' => '2',
            ],
            (object) [
                'id'             => 2,
                'title'          => (object) ['hr' => 'Američki dolar', 'en' => 'US Dollar'],
                'code'           => 'USD',
                'status'         => true,
                'main'           => false,
                'symbol_left'    => '$',
                'symbol_right'   => null,
                'value'          => '1.100000',
                'decimal_places' => '2',
            ],
        ], JSON_UNESCAPED_UNICODE);

// Languages
        $language_list = json_encode([
            (object) [
                'id'     => 1,
                'title'  => (object) ['hr' => 'Hrvatski', 'en' => 'Croatian'],
                'code'   => 'hr',
                'status' => true,
                'main'   => true,
            ],
            (object) [
                'id'     => 2,
                'title'  => (object) ['hr' => 'Engleski', 'en' => 'English'],
                'code'   => 'en',
                'status' => true,
                'main'   => false,
            ],
        ], JSON_UNESCAPED_UNICODE);

        // Order statuses
        $order_statuses = json_encode([
            (object) [
                'id'         => 1,
                'title'      => (object) ['hr' => 'Novo', 'en' => 'New'],
                'sort_order' => '0',
                'color'      => 'info',
            ],
            (object) [
                'id'         => 2,
                'title'      => (object) ['hr' => 'Čeka uplatu', 'en' => 'Pending Payment'],
                'sort_order' => '1',
                'color'      => 'warning',
            ],
            (object) [
                'id'         => 3,
                'title'      => (object) ['hr' => 'Plaćeno', 'en' => 'Paid'],
                'sort_order' => '3',
                'color'      => 'success',
            ],
            (object) [
                'id'         => 4,
                'title'      => (object) ['hr' => 'Poslano', 'en' => 'Shipped'],
                'sort_order' => '4',
                'color'      => 'secondary',
            ],
            (object) [
                'id'         => 5,
                'title'      => (object) ['hr' => 'Otkazano', 'en' => 'Cancelled'],
                'sort_order' => '5',
                'color'      => 'danger',
            ],
            (object) [
                'id'         => 6,
                'title'      => (object) ['hr' => 'Vraćeno', 'en' => 'Returned'],
                'sort_order' => '6',
                'color'      => 'dark',
            ],
            (object) [
                'id'         => 7,
                'title'      => (object) ['hr' => 'Odbijeno', 'en' => 'Rejected'],
                'sort_order' => '2',
                'color'      => 'danger',
            ],
            (object) [
                'id'         => 8,
                'title'      => (object) ['hr' => 'Nedovršena', 'en' => 'Incomplete'],
                'sort_order' => '7',
                'color'      => 'light',
            ],
        ], JSON_UNESCAPED_UNICODE);

// Taxes (array of objects, not keyed)
        $tax_list = json_encode([
            (object) [
                'id'         => 1,
                'geo_zone'   => 1,
                'title'      => (object) ['hr' => 'PDV 25%', 'en' => 'VAT 25%'],
                'rate'       => '25',
                'sort_order' => '0',
                'status'     => true,
            ],
        ], JSON_UNESCAPED_UNICODE);

// Geo zones (array of objects, not keyed)
        $geo_zones = json_encode([
            (object) [
                'id'          => 1,
                'status'      => true,
                'title'       => (object) ['hr' => 'Hrvatska', 'en' => 'Croatia'],
                'description' => null,
                'state'       => (object) ['2' => 'Croatia'],
            ],
            (object) [
                'id'          => 3,
                'status'      => true,
                'title'       => (object) ['hr' => 'Svijet', 'en' => 'World'],
                'description' => null,
                'state'       => (object) [],
            ],
        ], JSON_UNESCAPED_UNICODE);

        DB::insert(
            "INSERT INTO `settings` (`user_id`, `code`, `key`, `value`, `json`, `created_at`, `updated_at`) VALUES
                        (NULL, 'action',  'group_list', '" . $group_list . "', 1, NOW(), NOW()),
                        (NULL, 'action',  'type_list',  '" . $type_list . "', 1, NOW(), NOW()),
                        (NULL, 'payment', 'list.cod',   '[{\"title\":\"Gotovinom prilikom pouze\\u0107a\",\"code\":\"cod\",\"min\":\"10\",\"data\":{\"price\":\"5\",\"short_description\":\"Pla\\u0107anje gotovinom prilikom preuzimanja.\",\"description\":null},\"geo_zone\":\"1\",\"status\":true,\"sort_order\":\"2\"}]', 1, NOW(), NOW()),
                        (NULL, 'payment', 'list.bank',  '[{\"title\":\"Op\\u0107om uplatnicom \\/ Virmanom \\/ Internet bankarstvom\",\"code\":\"bank\",\"min\":null,\"data\":{\"price\":\"0\",\"short_description\":\"Uplatite direktno na na\\u0161 bankovni ra\\u010dun. Uputstva i uplatnice vam sti\\u017ee putem maila.\",\"description\":null},\"geo_zone\":null,\"status\":true,\"sort_order\":\"1\"}]', 1, NOW(), NOW()),
                        (NULL, 'payment', 'list.pickup','[{\"title\":\"Platite prilikom preuzimanja\",\"code\":\"pickup\",\"min\":null,\"data\":{\"price\":\"0\",\"short_description\":\"Platiti mo\\u017eete gotovinom ili karticama na POS ure\\u0111ajima\",\"description\":null},\"geo_zone\":\"1\",\"status\":true,\"sort_order\":\"0\"}]', 1, NOW(), NOW()),
                        (NULL, 'currency','list',       '" . $currency_list . "', 1, NOW(), NOW()),
                        (NULL, 'language','list',       '" . $language_list . "', 1, NOW(), NOW()),
                        (NULL, 'order',   'statuses',   '" . $order_statuses . "', 1, NOW(), NOW()),
                        (NULL, 'tax',     'list',       '" . $tax_list . "', 1, NOW(), NOW()),
                        (NULL, 'geozone', 'list',       '" . $geo_zones . "', 1, NOW(), NOW())"
        );

    }
}
