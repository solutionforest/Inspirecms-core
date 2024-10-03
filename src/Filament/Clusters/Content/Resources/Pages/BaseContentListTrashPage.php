<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages;

use Filament\Tables\Table;

abstract class BaseContentListTrashPage extends BaseContentListPage
{
    public function getActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [];
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->query(
                fn () => $this->getTableQuery()
                    ->onlyTrashed()
            );
    }
}
