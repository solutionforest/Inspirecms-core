<?php

namespace SolutionForest\InspireCms\Base\Filament\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;

class BaseChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

    protected static ?string $inverseRelationship = 'parent';

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
            ->pluralModelLabel(fn () => __('inspirecms::inspirecms.children'))
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

        if ($this->isRedirectToCreatePage()) {

            $resource = $this->getPageClass()::getResource();

            $parameters = ['parent' => $this->getOwnerRecord()->getKey()];

            $url = FilamentResourceHelper::attemptToGetUrl($resource, ['create'], $parameters, false);

            if ($url) {
                $action->url($url);
            }

        }

        $action
            ->slideOver()
            ->modalWidth('7xl');
    }

    protected function configureEditAction(Tables\Actions\EditAction $action): void
    {
        parent::configureEditAction($action);

        if ($this->isRedirectToDetailPage()) {
            $resource = $this->getPageClass()::getResource();

            $action->url(
                fn ($record) => FilamentResourceHelper::attemptToGetUrl($resource, 'edit', ['record' => $record], false)
            );
        }

        $action
            ->slideOver()
            ->modalWidth('7xl');
    }

    protected function isRedirectToDetailPage(): bool
    {
        return false;
    }

    protected function isRedirectToCreatePage(): bool
    {
        return true;
    }
}
