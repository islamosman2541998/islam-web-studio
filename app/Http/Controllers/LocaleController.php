<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale)
    {
        abort_unless(in_array($locale, ['ar', 'en']), 404);
        $request->session()->put('studio_locale', $locale);
        $request->user()?->update(['locale' => $locale]);
        $back = $request->input('back', '/admin');
        if (! str_starts_with($back, '/admin') || str_starts_with($back, '//')) {
            $back = '/admin';
        }

return redirect($back);
    }
}
