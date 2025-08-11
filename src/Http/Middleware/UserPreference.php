<?php

namespace SolutionForest\InspireCms\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

class UserPreference
{
    public function handle(Request $request, Closure $next)
    {
        $guard = Filament::auth();

        if ($guard->check() && ($user = $guard->user()) && is_inspirecms_user($user)) {

            $locale = $user->preferred_language ?? config('app.locale');

            app()->setLocale($locale);
        }

        return $next($request);
    }
}
