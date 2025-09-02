<?php

namespace SolutionForest\InspireCms\Filament\Resources\UserResource\Pages;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\Authenticatable;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseCreateRecord;
use SolutionForest\InspireCms\Filament\Resources\UserResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class CreateUser extends BaseCreateRecord
{
    public function getActions(): array
    {
        return [];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('user', UserResource::class);
    }

    public function afterCreate()
    {
        $user = $this->record;

        if ($user && $user instanceof Authenticatable) {
            event(new Registered($user));
        }
    }
}
