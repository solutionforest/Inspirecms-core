<?php

namespace SolutionForest\InspireCms\Http\Middleware;

use Filament\Http\Middleware\Authenticate as Middleware;
use SolutionForest\InspireCms\Facades\InspireCms;

class CmsAuthenticate extends Middleware
{
    protected function redirectTo($request): ?string
    {
        if (InspireCms::needInstall()) {
            return InspireCms::getInstallUrl();
        }

        return parent::redirectTo($request);
    }
}
