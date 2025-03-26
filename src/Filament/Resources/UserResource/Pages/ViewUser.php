<?php

namespace SolutionForest\InspireCms\Filament\Resources\UserResource\Pages;

use SolutionForest\InspireCms\Base\Filament\Pages\Concerns\ProfilePageTrait;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseViewRecord;
use SolutionForest\InspireCms\Filament\Resources\UserResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class ViewUser extends BaseViewRecord
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
