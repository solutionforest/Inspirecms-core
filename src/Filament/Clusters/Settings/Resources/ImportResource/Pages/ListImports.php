<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\ImportResource\Pages;

use Filament\Actions;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListPage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\ImportResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class ListImports extends BaseListPage
{
    public function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->createAnother(false)
                ->modalWidth('7xl')
                ->stickyModalHeader()->stickyModalHeader()
                ->slideOver()
                ->label(__('inspirecms::resources/import.actions.import.label'))
                ->modalSubmitActionLabel(__('inspirecms::resources/import.actions.import.modal.actions.submit.label')),
        ];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('import', ImportResource::class);
    }
}
