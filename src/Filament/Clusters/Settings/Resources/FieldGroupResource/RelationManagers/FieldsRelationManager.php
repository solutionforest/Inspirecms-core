<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource\RelationManagers;

use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
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
            ->stickyModalHeader()
            // Since may have bugs on filament v3.2.108
            ->createAnother(false);
    }

    public static function getIcon(Model $ownerRecord, string $pageClass): ?string
    {
        return null;
    }
}
