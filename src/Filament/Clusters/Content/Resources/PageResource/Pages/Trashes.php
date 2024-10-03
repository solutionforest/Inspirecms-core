<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource\Pages;

use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages\BaseContentListTrashPage;

class Trashes extends BaseContentListTrashPage
{
    public static function getResource(): string
    {
        return config('inspirecms.resources.page', PageResource::class);
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
}
