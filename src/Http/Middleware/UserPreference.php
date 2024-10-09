<?php

namespace SolutionForest\InspireCms\Http\Middleware;

class UserPreference
{
    public function handle($request, \Closure $next)
    {
        if (auth()->check() && is_inspirecms_user(auth()->user())) {
            $locale = auth()->user()->preferred_language ?? config('app.locale');
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
