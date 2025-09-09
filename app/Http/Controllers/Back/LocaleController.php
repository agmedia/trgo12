<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function __invoke(Request $request)
    {
        $locale = $request->string('locale', 'en')->toString();
        $allowed = config('shop.supported_locales', ['en','hr']);
        abort_unless(in_array($locale, $allowed, true), 400);

        session(['locale' => $locale]);
        app()->setLocale($locale);

        return back();
    }
}
