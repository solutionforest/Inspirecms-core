<?php

namespace SolutionForest\InspireCms\Base\Filament\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;

class BaseChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

    protected static ?string $inverseRelationship = 'parent';

    public function form(Schema $schema): Schema
    {
        $resource = $this->getPageClass()::getResource();

        return $resource::form($schema);
    }

    public function table(Table $table): Table
    {
        $resource = $this->getPageClass()::getResource();

        return $resource::table($table)
            ->modelLabel(lcfirst(__('inspirecms::inspirecms.children.singular')))
            ->pluralModelLabel(lcfirst(__('inspirecms::inspirecms.children.plural')))
            ->emptyStateHeading(null)
            ->headerActions([
                CreateAction::make()
                    ->slideOver()
                    ->modalWidth('7xl'),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('inspirecms::inspirecms.children.plural');
    }

    public function getDefaultActionUrl(Action $action): ?string
    {
        $relatedResource = static::getRelatedResource() ?? $this->getPageClass()::getResource();

        if (! $relatedResource) {
            return null;
        }

        if (
            ($action instanceof CreateAction)
        ) {

            if ($this->isRedirectToCreatePage()) {

                $parameters = ['parent' => $this->getOwnerRecord()->getKey()];

                return FilamentResourceHelper::attemptToGetUrl($relatedResource, ['create'], $parameters, false);
            }

            if ($relatedResource::hasPage('create')) {
                return $relatedResource::getUrl('create', shouldGuessMissingParameters: true);
            }
        }

        if ($action instanceof EditAction) {

            if ($this->isRedirectToDetailPage()) {
                return FilamentResourceHelper::attemptToGetUrl($relatedResource, 'edit', ['record' => $action->getRecord()], false);
            } elseif ($relatedResource::hasPage('edit')) {
                return $relatedResource::getUrl('edit', ['record' => $action->getRecord()], shouldGuessMissingParameters: true);
            }

        }

        // if (
        //     ($action instanceof EditAction) &&
        //     ($relatedResource::hasPage('edit'))
        // ) {
        //     return $relatedResource::getUrl('edit', ['record' => $action->getRecord()], shouldGuessMissingParameters: true);
        // }

        if (
            ($action instanceof ViewAction) &&
            ($relatedResource::hasPage('view'))
        ) {
            return $relatedResource::getUrl('view', ['record' => $action->getRecord()], shouldGuessMissingParameters: true);
        }

        return null;
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
