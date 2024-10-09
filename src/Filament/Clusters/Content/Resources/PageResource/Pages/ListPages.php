<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource\Pages;

use Filament\Actions;
use Filament\Tables;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages\BaseContentListPage;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;

class ListPages extends BaseContentListPage
{
    public function getActions(): array
    {
        return [
            ...parent::getActions(),
            Actions\Action::make('trash')
                ->label(__('inspirecms::actions.trash.label'))
                ->url(fn () => FilamentResourceHelper::attemptToGetUrl(static::getResource(), 'trash', [], false))
                ->color('gray')
                ->icon('heroicon-o-trash'),
        ];
    }

    public static function getResource(): string
    {
        return config('inspirecms.filament.resources.page', PageResource::class);
    }

    protected function configureCreateAction(Actions\CreateAction | Tables\Actions\CreateAction $action): void
    {
        parent::configureCreateAction($action);

        $action->modelLabel(strtolower(__('inspirecms::inspirecms.content')));

        $action->url(function () {
            $resource = static::getResource();

            return FilamentResourceHelper::attemptToGetUrl($resource, ['create'], ['parent' => $this->parent], false);
        });
    }

    protected function configureDeleteAction(Tables\Actions\DeleteAction $action): void
    {
        parent::configureDeleteAction($action);

        $action->successRedirectUrl($this->getUrl());
    }

    protected function configureForceDeleteAction(Tables\Actions\ForceDeleteAction $action): void
    {
        parent::configureForceDeleteAction($action);

        $action->successRedirectUrl($this->getUrl());
    }

    protected function configureDeleteBulkAction(Tables\Actions\DeleteBulkAction $action): void
    {
        parent::configureDeleteBulkAction($action);

        $action->successRedirectUrl($this->getUrl());
    }

    protected function configureForceDeleteBulkAction(Tables\Actions\ForceDeleteBulkAction $action): void
    {
        parent::configureForceDeleteBulkAction($action);

        $action->successRedirectUrl($this->getUrl());
    }

    protected function configureRestoreBulkAction(Tables\Actions\RestoreBulkAction $action): void
    {
        parent::configureRestoreBulkAction($action);

        $action->successRedirectUrl($this->getUrl());
    }
}
