<?php

namespace App\Http\Controllers\Back;


use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('back.dashboard'/*, compact('data', 'orders', 'bestsellers', 'products', 'this_year', 'last_year')*/);
    }


    public function maintenanceOn(): RedirectResponse
    {
        // Tajni "bypass" ključ da se ne zaključaš van
        $secret = 'agm';

        // Laravel maintenance ON (sa bypass URL-om /{secret})
        Artisan::call('down', [
            '--secret' => $secret,
            '--retry'  => 60, // Retry-After header (sekunde)
        ]);

        Cache::put('maintenance:secret', $secret, now()->addHours(6));

        return back()->with('success', 'Maintenance ON. Bypass URL: ' . url($secret));
    }

    public function maintenanceOff(): RedirectResponse
    {
        Artisan::call('up');
        Cache::forget('maintenance:secret');

        return back()->with('success', 'Maintenance OFF.');
    }

    public function clearCache(): RedirectResponse
    {
        // Briše sve bitne cacheve odjednom
        Artisan::call('optimize:clear');

        return back()->with('success', 'Cache, config, route i view cache su očišćeni.');
    }

}
