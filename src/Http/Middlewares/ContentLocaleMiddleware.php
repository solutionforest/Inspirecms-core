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

        if (! blank($currentLocale)) {
            $request->setLocale($currentLocale);
        }

        $request->setDefaultLocale(
            InspireCms::getFallbackLanguage()?->locale ?? config('app.locale')
        );

        return $next($request);
    }
}
