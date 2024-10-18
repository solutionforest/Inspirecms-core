<?php

namespace SolutionForest\InspireCms\Http\Middlewares;

use Illuminate\Http\Request;

class UserPreference
{
    public function handle(Request $request, \Closure $next)
    {
        if (auth()->check() && is_inspirecms_user(auth()->user())) {
            $locale = auth()->user()->preferred_language ?? config('app.locale');
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
