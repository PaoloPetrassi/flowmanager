<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Store the preferred interface language.
     */
    public function update(Request $request, string $locale): RedirectResponse
    {
        abort_unless(
            array_key_exists($locale, config('app.supported_locales', [])),
            404
        );

        $request->session()->put('locale', $locale);

        return back()->withCookie(
            cookie('locale', $locale, 60 * 24 * 365)
        );
    }
}
