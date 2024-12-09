<?php

namespace SolutionForest\InspireCms\Base\Filament\Resources\Pages;

use Filament\Resources\Pages\ManageRecords;
use SolutionForest\InspireCms\InspireCmsConfig;

class BaseManagePage extends ManageRecords
{
    public function getSubNavigation(): array
    {
        if (InspireCmsConfig::get('filament.enable_cluster_navigation')) {
            return parent::getSubNavigation();
        }

        return [];
    }
}
