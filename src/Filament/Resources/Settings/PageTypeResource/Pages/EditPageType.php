<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings\PageTypeResource\Pages;

use Filament\Actions;
use SolutionForest\InspireCms\Filament\Resources\Pages\EditWithDetailInfoPage;
use SolutionForest\InspireCms\Filament\Resources\Settings\PageTypeResource;

class EditPageType extends EditWithDetailInfoPage
{
    public function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public static function getResource(): string
    {
        return config('inspirecms-core.resources.page_type', PageTypeResource::class);
    }
}
