<?php

namespace SolutionForest\InspireCms\Base\Filament\Resources\Pages;

use Filament\Resources\Pages\ListRecords;
use SolutionForest\InspireCms\InspireCmsConfig;

class BaseListPage extends ListRecords
{
    public function getSubNavigation(): array
    {
        if (InspireCmsConfig::get('filament.enable_cluster_navigation')) {
            return parent::getSubNavigation();
        }

        return [];
    }
}
