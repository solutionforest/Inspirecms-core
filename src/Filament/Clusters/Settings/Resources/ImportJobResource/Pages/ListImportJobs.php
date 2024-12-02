<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\ImportJobResource\Pages;

use Filament\Actions;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListPage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\ImportJobResource;

class ListImportJobs extends BaseListPage
{
    public function getActions(): array
    {
        return [
            Actions\CreateAction::make()->createAnother(false)->modalWidth('7xl')->stickyModalHeader()->stickyModalHeader(),
        ];
    }

    public static function getResource(): string
    {
        return config('inspirecms.filament.resources.import_job', ImportJobResource::class);
    }
}
