<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale') ?? $request->session()->get('studio_locale', 'ar');
        if (! in_array($locale, ['ar', 'en'])) {
            $locale = 'ar';
        }app()->setLocale($locale);
        $request->session()->put('studio_locale', $locale);

        return $next($request);
    }
}
