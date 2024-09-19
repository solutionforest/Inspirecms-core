<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\ElementResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

    // public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    // {
    //     if (! parent::canViewForRecord($ownerRecord, $pageClass)) {
    //         return false;
    //     }

    //     return $ownerRecord->is_element_type == true;
    // }

    public function form(Form $form): Form
    {
        $resource = $this->getPageClass()::getResource();

        return $resource::form($form);
    }

    public function table(Table $table): Table
    {
        $resource = $this->getPageClass()::getResource();

        return $resource::table($table)
            ->modelLabel(__('inspirecms::inspirecms.children'))
            ->emptyStateHeading(null)
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('inspirecms::inspirecms.children');
    }

    // protected function configureTableAction(\Filament\Tables\Actions\Action $action): void
    // {
    //     match (true) {
    //         $action instanceof CloneAction => $this->configureCloneAction($action),
    //         $action instanceof QuickEditAction => $this->configureQuickEditAction($action
    //             ->slideOver()
    //             ->modalWidth('7xl')),
    //         default => parent::configureTableAction($action),
    //     };
    // }

    protected function configureCreateAction(Tables\Actions\CreateAction $action): void
    {
        parent::configureCreateAction($action);

        $action
            ->slideOver()
            ->modalWidth('7xl');
    }

    protected function configureEditAction(Tables\Actions\EditAction $action): void
    {
        parent::configureEditAction($action);

        $action
            ->slideOver()
            ->modalWidth('7xl');
    }
}
