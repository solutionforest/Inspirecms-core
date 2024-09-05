<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Users\Resources\UserResource\Pages;

use Filament\Resources\Pages\EditRecord;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\UserResource;
use SolutionForest\InspireCms\Filament\Pages\Auth\Concerns\ProfilePageTrait;

class EditUser extends EditRecord
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
