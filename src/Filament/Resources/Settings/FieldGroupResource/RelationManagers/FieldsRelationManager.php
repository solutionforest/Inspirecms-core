<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings\FieldGroupResource\RelationManagers;

use Filament\Tables;
use SolutionForest\FilamentFieldGroup\Filament\Resources\FieldGroupResource\RelationManagers\FieldsRelationManager as BaseRelationManager;

class FieldsRelationManager extends BaseRelationManager
{
    protected function configureEditAction(Tables\Actions\EditAction $action): void
    {
        parent::configureEditAction($action);

        $action
            ->slideOver()
            ->stickyModalHeader();
    }

    protected function configureCreateAction(Tables\Actions\CreateAction $action): void
    {
        parent::configureCreateAction($action);

        $action
            ->slideOver()
            ->stickyModalHeader();
    }
}
