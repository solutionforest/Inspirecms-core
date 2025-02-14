<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Concerns;

use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages\BaseContentCreatePage;

trait ContentPageTrait
{
    public function initializeContentPageTrait()
    {
        $this->listeners = array_merge($this->listeners, [
            'changeActiveLocale',
        ]);
    }

    public function mountContentPageTrait(): void
    {
        if (blank($this->activeLocale)) {
            if ($this instanceof \Filament\Resources\Pages\CreateRecord || $this instanceof \Filament\Resources\Pages\ListRecords) {
                $this->activeLocale = static::getResource()::getDefaultTranslatableLocale();
            } else {
                $this->activeLocale = $this->getDefaultTranslatableLocale();
            }
        }
    }

    public function changeActiveLocale(string $locale)
    {
        $this->activeLocale = $locale;
    }

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

    public function getLayout(): string
    {
        return 'inspirecms::components.layout.content-page';
    }

    protected function getLayoutData(): array
    {
        $selectedModelItemKey = null;
        $expandedModelItemKeys = [];

        if ($this instanceof EditRecord || $this instanceof ViewRecord) {
            $record = $this->getRecord();
            $selectedModelItemKey = $record->getKey();
            $expandedModelItemKeys[] = $record->parent_id;
        } elseif ($this instanceof BaseContentCreatePage) {
            $selectedModelItemKey = $this->parent;
        }

        return [
            'redirectUrlParameters' => $this->getRedirectUrlParameters(),
            'activeLocale' => $this->activeLocale, // from queryString
            'selectedModelItemKeys' => array_filter([$selectedModelItemKey]),
            'expandedModelItemKeys' => $expandedModelItemKeys,
            'pageName' => match (true) {
                $this instanceof EditRecord => 'edit',
                $this instanceof ViewRecord => 'view',
                $this instanceof BaseContentCreatePage => 'create',
                default => 'index',
            },
        ];
    }
}
