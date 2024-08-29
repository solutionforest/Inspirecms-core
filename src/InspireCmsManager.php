<?php

namespace SolutionForest\InspireCms;

use Filament\Facades\Filament;
use Illuminate\Auth\EloquentUserProvider;
use SolutionForest\InspireCms\Filament\Pages\Auth\Install;

class InspireCmsManager 
{
    /**
     * Determine if there is a need to go to the install page
     */
    public function needInstall(): bool
    {
        //region Check user table not empty
        $guard = config('inspirecms.auth.guard', 'inspirecms');

        /** @var ?EloquentUserProvider $provider */
        $provider = auth($guard)?->getProvider();

        if (!$provider) {
            throw new \Exception('Authentication provider not found for guard: ' . $guard);
        }
        if ($provider->getModel()::count() <= 0 ) {
            return true;
        }
        //endregion Check user table not empty

        return false;
    }

    public function getInstallUrl(): ?string
    {
        return Filament::getPanel('cms')?->route(Install::getRouteSlug());
    }
}
