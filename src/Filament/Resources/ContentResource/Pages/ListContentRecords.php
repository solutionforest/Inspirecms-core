<?php

namespace SolutionForest\InspireCms\Filament\Resources\ContentResource\Pages;

use Filament\Actions\CreateAction;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseContentListPage;
use SolutionForest\InspireCms\Filament\Actions\TrashBinAction;
use SolutionForest\InspireCms\Filament\Resources\ContentResource;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

class ListContentRecords extends BaseContentListPage
{
    public function getActions(): array
    {
        return collect([
            ...parent::getActions(),
            TrashBinAction::make()
                ->url(fn () => FilamentResourceHelper::attemptToGetUrl(static::getResource(), 'trash', [], false)),
        ])->map(function ($action) {
            if ($action instanceof CreateAction) {

                $action->url(function () {
                    $resource = static::getResource();

                    return FilamentResourceHelper::attemptToGetUrl($resource, ['create'], ['parent' => $this->getParentKey(), ...$this->getRedirectUrlParameters()], false);
                });
            }

            return $action;
        })->all();
    }

    protected function getHeaderWidgets(): array
    {
        return static::getResource()::getWidgets();
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('content', ContentResource::class);
    }
}
