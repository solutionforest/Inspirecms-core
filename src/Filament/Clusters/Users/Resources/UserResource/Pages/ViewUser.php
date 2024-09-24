<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Users\Resources\UserResource\Pages;

use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseViewPage;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\UserResource;
use SolutionForest\InspireCms\Filament\Pages\Auth\Concerns\ProfilePageTrait;

class ViewUser extends BaseViewPage
{
    use ProfilePageTrait;

    public static function getResource(): string
    {
        return config('inspirecms.resources.user', UserResource::class);
    }

    public static function isSimple(): bool
    {
        return false;
    }
}
