<?php

namespace SolutionForest\InspireCms\Http\Middlewares;

use Illuminate\Http\Request;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Factories\ContentUrlGeneratorFactory;

class ContentLocaleMiddleware
{
    public function handle(Request $request, $next)
    {
        $currentLocale = ContentUrlGeneratorFactory::create()->getLocaleFromRequest($request);

        $lang = ! blank($currentLocale) 
            ? collect(InspireCms::getAllAvailableLanguages())->firstWhere(fn ($lang) => $lang->locale == $currentLocale)
            : null;
            
        if ($lang) {
            $request->setLocale($lang->code);
        }

        $request->setDefaultLocale(
            InspireCms::getFallbackLanguage()?->locale ?? config('app.locale')
        );

        return $next($request);
    }
}
