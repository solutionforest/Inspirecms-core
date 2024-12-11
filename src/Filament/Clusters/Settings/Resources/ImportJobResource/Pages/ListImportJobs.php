<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\ImportJobResource\Pages;

use Filament\Actions;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListPage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\ImportJobResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class ListImportJobs extends BaseListPage
{
    public function getActions(): array
    {
        return [
            Actions\CreateAction::make()->createAnother(false)->modalWidth('7xl')->stickyModalHeader()->stickyModalHeader()->slideOver(),
        ];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('import_job', ImportJobResource::class);
    }
}
