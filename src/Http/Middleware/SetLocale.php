<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // // Get language from path
        // $routeLanguageId = request()->segment(1);
        // if (strlen($routeLanguageId) === 2 && array_key_exists($routeLanguageId, config('cms.available_locales'))) {
        //     $languageId = $routeLanguageId;
        // }

        if ($request->user()) {
            // Update users language
            if (!empty($languageId) && array_key_exists($languageId, config('cms.available_locales')) && $request->user()->language != $languageId) {
                User::where(['id' => $request->user()->id])->update(['language' => $languageId]);
            } else {
                // Get users language
                $languageId = $request->user()->language;
            }
        }

        // Get language from session
        if (empty($languageId) && session()->get('languageId')) {
            $languageId = session()->get('languageId');
        }

        // Get language from browser
        if (empty($languageId) && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            $firstLang = substr($acceptLanguage, 0, strpos($acceptLanguage, ',') ?: strlen($acceptLanguage));
            $langParts = explode('-', $firstLang);
            $acceptLanguageId = isset($langParts[0]) ? strtolower($langParts[0]) : null;
            if (strlen($acceptLanguageId) === 2 && array_key_exists($acceptLanguageId, config('cms.available_locales'))) {
                $languageId = $acceptLanguageId;
            }
        }

        // Use fallback language
        if (empty($languageId) || !array_key_exists($languageId, config('cms.available_locales'))) {
            $languageId = config('cms.fallback_locale');
        }

        // Set language
        app()->setLocale($languageId);

        // Store in session
        session()->put('languageId', $languageId);

        return $next($request);
    }
}
