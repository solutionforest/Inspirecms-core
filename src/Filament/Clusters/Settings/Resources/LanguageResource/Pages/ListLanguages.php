<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\LanguageResource\Pages;

use Filament\Actions;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListPage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\LanguageResource;

class ListLanguages extends BaseListPage
{
    public function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.language', LanguageResource::class);
    }
}
