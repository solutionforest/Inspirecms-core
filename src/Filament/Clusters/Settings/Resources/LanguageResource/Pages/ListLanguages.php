<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\LanguageResource\Pages;

use Filament\Actions;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListPage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\LanguageResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class ListLanguages extends BaseListPage
{
    public function getActions(): array
    {
        return [
            Actions\CreateAction::make()->slideOver(),
        ];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('language', LanguageResource::class);
    }
}
