<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Resources\Concerns\HasCloneAction;
use SolutionForest\InspireCms\Filament\Resources\Concerns\HasQuickEditAction;
use SolutionForest\InspireCms\Filament\Tables\Actions\CloneAction;
use SolutionForest\InspireCms\Filament\Tables\Actions\QuickEditAction;

class ChildrenRelationManager extends RelationManager
{
    use HasCloneAction;
    use HasQuickEditAction;

    protected static string $relationship = 'children';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        if (! parent::canViewForRecord($ownerRecord, $pageClass)) {
            return false;
        }

        return $ownerRecord->is_element_type == true;
    }

    public function form(Form $form): Form
    {
        $resource = $this->getPageClass()::getResource();

        return $resource::childrenForm($form);
    }

    public function table(Table $table): Table
    {
        $resource = $this->getPageClass()::getResource();

        return $resource::table($table)
            ->modelLabel(__('inspirecms::inspirecms.children'))
            ->emptyStateHeading(null)
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id'))
                    ->width('1%')->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('inspirecms::inspirecms.name'))
                    ->sortable(),

                // timestamps
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('inspirecms::inspirecms.created_at'))
                    ->sortable()
                    ->formatStateUsing(fn (?\Carbon\Carbon $state) => $state?->diffForHumans())
                    ->width('5%'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('inspirecms::inspirecms.last_updated_at'))
                    ->sortable()
                    ->formatStateUsing(fn (?\Carbon\Carbon $state) => $state?->diffForHumans())
                    ->width('5%'),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('inspirecms::inspirecms.children');
    }

    protected function configureTableAction(\Filament\Tables\Actions\Action $action): void
    {
        match (true) {
            $action instanceof CloneAction => $this->configureCloneAction($action),
            $action instanceof QuickEditAction => $this->configureQuickEditAction($action
                ->slideOver()
                ->modalWidth('7xl')),
            default => parent::configureTableAction($action),
        };
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
