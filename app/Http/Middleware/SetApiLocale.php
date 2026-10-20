<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** API language comes from `?lang=` or the `Accept-Language` header (ar by default); there is no session. */
class SetApiLocale
{
    public const LOCALES = ['ar', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $requested = $request->query('lang') ?: $request->getPreferredLanguage(self::LOCALES);
        $locale = in_array($requested, self::LOCALES, true) ? $requested : 'ar';
        app()->setLocale($locale);

        $response = $next($request);
        $response->headers->set('Content-Language', $locale);
        $response->headers->set('Vary', trim($response->headers->get('Vary').', Accept-Language', ', '));

        return $response;
    }
}
