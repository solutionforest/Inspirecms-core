<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Concerns;

use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages\BaseContentCreatePage;

trait ContentPageTrait
{
    protected function queryStringContentPageTrait()
    {
        return [
            'activeLocale' => ['as' => 'locale'],
        ];
    }

    public function getSubNavigation(): array
    {
        return [];
    }

    protected function getRedirectUrlParameters(): array
    {
        return [
            'activeRelationManager' => 0,
            'locale' => $this->activeLocale,
        ];
    }

    protected function getLayoutData(): array
    {
        $selectedModelItemKey = null;

        if ($this instanceof EditRecord || $this instanceof ViewRecord) {
            $selectedModelItemKey = $this->getRecord()->getKey();
        } elseif ($this instanceof BaseContentCreatePage) {
            $selectedModelItemKey = $this->parent;
        }

        return [
            'redirectUrlParameters' => $this->getRedirectUrlParameters(),
            'activeLocale' => $this->activeLocale,
            'selectedModelItemKey' => $selectedModelItemKey,
            'pageName' => match (true) {
                $this instanceof EditRecord => 'edit',
                $this instanceof ViewRecord => 'view',
                $this instanceof BaseContentCreatePage => 'create',
                default => 'index',
            },
        ];
    }
}
