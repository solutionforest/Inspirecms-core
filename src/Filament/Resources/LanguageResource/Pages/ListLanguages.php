<?php

namespace SolutionForest\InspireCms\Filament\Resources\LanguageResource\Pages;

use Filament\Actions;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListRecords;
use SolutionForest\InspireCms\Filament\Resources\LanguageResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class ListLanguages extends BaseListRecords
{
    public function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('language', LanguageResource::class);
    }
}
