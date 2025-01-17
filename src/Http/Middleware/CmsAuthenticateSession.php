<?php

namespace SolutionForest\InspireCms\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\AuthenticateSession as Middleware;
use SolutionForest\InspireCms\Facades\InspireCms;

class CmsAuthenticateSession extends Middleware
{
    /**
     * Get the path the user should be redirected to when their session is not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo(Request $request)
    {
        if (InspireCms::needInstall()) {
            return InspireCms::getInstallUrl();
        }

        return Filament::getLoginUrl();
    }
}
