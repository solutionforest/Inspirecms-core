<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Tables\Actions\CreateAction as ActionsCreateAction;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages\BaseContentListPage;

class ListPages extends BaseContentListPage
{
    public static function getResource(): string
    {
        return config('inspirecms.resources.page', PageResource::class);
    }

    protected function configureCreateAction(CreateAction | ActionsCreateAction $action): void
    {
        parent::configureCreateAction($action);

        $action->modelLabel(strtolower(__('inspirecms::inspirecms.content')));

        $action->url(function () {
            $resource = static::getResource();

            return $resource::getUrl('create', ['parent' => $this->parent]);
        });
    }
}
