<?php

namespace App\Http\Controllers\Back\Settings\Shop;

use App\Http\Controllers\Back\Settings\Base\AbstractSettingsListPageController;
use App\Models\Back\Settings\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GeozonePageController extends AbstractSettingsListPageController
{
    protected string $sectionCode = 'geozones';
    protected string $view        = 'back.settings.shop.geozone.index';

    public function edit($id)
    {
        $locales = config('shop.locales', ['hr'=>'Hrvatski','en'=>'English']);

        // full list
        $all     = $this->readAll();
        $geozone = $all->firstWhere('id', (int)$id);
        if (!$geozone) abort(404);

        // countries/zones data sources (adjust if you load from tables/files)
        $countries = collect(config('settings.countries', [])); // or your repository
        $zones     = collect(config('settings.zones', []));     // or your repository

        // Preselects: legacy $geozone->state is map id=>name (ids can be country or zone)
        $selectedCountryIds = [];
        $selectedZoneIds    = [];
        if (!empty($geozone->state) && is_object($geozone->state)) {
            foreach ((array)$geozone->state as $key => $name) {
                $id = (int)$key;
                // naive split: treat ids >= 1000 as zone? (or better: detect by presence in zones)
                if ($zones->firstWhere('id', $id)) $selectedZoneIds[] = $id;
                elseif ($countries->firstWhere('id', $id)) $selectedCountryIds[] = $id;
            }
        }

        return view('back.settings.shop.geozone.edit', [
            'item'               => $geozone,
            'locales'            => $locales,
            'countries'          => $countries,
            'zones'              => $zones,
            'selectedCountries'  => $selectedCountryIds,
            'selectedZones'      => $selectedZoneIds,
        ]);
    }
}
