<?php

namespace SolutionForest\InspireCms\Base\Filament\Resources\Pages;

use Filament\Resources\Pages\ListRecords;

class BaseListPage extends ListRecords
{
    public function getSubNavigation(): array
    {
        return [];
    }
}
