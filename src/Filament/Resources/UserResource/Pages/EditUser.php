<?php

namespace SolutionForest\InspireCms\Filament\Resources\UserResource\Pages;

use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseEditRecord;
use SolutionForest\InspireCms\Filament\Pages\Auth\Concerns\ProfilePageTrait;
use SolutionForest\InspireCms\Filament\Resources\UserResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class EditUser extends BaseEditRecord
{
    use ProfilePageTrait;

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('user', UserResource::class);
    }

    public static function isSimple(): bool
    {
        return false;
    }
}
