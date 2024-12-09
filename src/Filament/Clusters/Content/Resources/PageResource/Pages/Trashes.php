<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource\Pages;

use Filament\Actions;
use Filament\Tables;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages\BaseContentListTrashPage;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

class Trashes extends BaseContentListTrashPage
{
    public function getActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label(__('inspirecms::inspirecms.back'))
                ->url(fn () => FilamentResourceHelper::attemptToGetUrl(static::getResource(), 'index', [], false))
                ->color('gray'),
            ...parent::getActions(),
        ];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('page', PageResource::class);
    }

    protected function configureTableBulkAction(Tables\Actions\BulkAction $action): void
    {
        parent::configureTableBulkAction($action);

        $action->successRedirectUrl($this->getUrl());
    }

    protected function configureTableAction(Tables\Actions\Action $action): void
    {
        parent::configureTableAction($action);

        $action->successRedirectUrl($this->getUrl());
    }

    public function getBreadcrumb(): ?string
    {
        return __('inspirecms::inspirecms.trash');
    }

    public function getTitle(): string
    {
        return __('inspirecms::inspirecms.trash');
    }
}
