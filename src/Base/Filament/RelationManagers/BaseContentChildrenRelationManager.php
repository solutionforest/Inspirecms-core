<?php

namespace SolutionForest\InspireCms\Base\Filament\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BaseContentChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

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
