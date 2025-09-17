<?php

namespace SolutionForest\InspireCms\Base\Filament\Concerns;

use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseContentCreatePage;
use SolutionForest\InspireCms\Helpers\UIHelper;

trait ContentPageTrait
{
    public function bootContentPageTrait()
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::CONTENT_BEFORE,
            function () {

                $livewireData = $this->getLivewireData();

                return Blade::render(<<<Blade
                    <livewire:inspirecms::content-sidebar :data="\$livewireData" />
                Blade, [
                    'livewireData' => $livewireData,
                ]);
            },
            [static::class],
        );
    }
    
    public function initializeContentPageTrait()
    {
        $this->listeners = array_merge($this->listeners, [
            'changeActiveLocale',
        ]);
    }

    public function mountContentPageTrait(): void
    {
        if (blank($this->activeLocale)) {
            if ($this instanceof CreateRecord || $this instanceof ListRecords) {
                $this->activeLocale = static::getResource()::getDefaultTranslatableLocale();
            } else {
                $this->activeLocale = $this->getDefaultTranslatableLocale();
            }
        }
    }

    public function getTitle(): string | Htmlable
    {
        $title = parent::getTitle();

        if (($this instanceof EditRecord || $this instanceof ViewRecord) && $this->getRecord()->isLocked()) {
            return UIHelper::generateTextWithBadge(
                text: $title,
                badgeText: __('inspirecms::messages.locked'),
                color: 'warning',
                icon: FilamentIcon::resolve('inspirecms::locked'),
            );
        }

        return $title;
    }

    /**
     * @return array<string>
     */
    public function getBreadcrumbs(): array
    {
        /**
         * @var class-string<resource>
         */
        $resource = static::getResource();

        $breadcrumbs = [
            $resource::getUrl() => $resource::getBreadcrumb(),
        ];

        if (method_exists($this, 'getRecord')) {

            $record = $this->getRecord();

            if (($parentRecord = $record?->parent) && $resource::hasRecordTitle()) {
                if ($resource::hasPage('view') && $resource::canView($parentRecord)) {
                    $breadcrumbs[
                        $resource::getUrl('view', ['record' => $parentRecord, ...$this->getRedirectUrlParameters()])
                    ] = $resource::getRecordTitle($parentRecord);
                } elseif ($resource::hasPage('edit') && $resource::canEdit($parentRecord)) {
                    $breadcrumbs[
                        $resource::getUrl('edit', ['record' => $parentRecord, ...$this->getRedirectUrlParameters()])
                    ] = $resource::getRecordTitle($parentRecord);
                } else {
                    $breadcrumbs[] = $resource::getRecordTitle($parentRecord);
                }
            }

            if ($record?->exists && $resource::hasRecordTitle()) {
                if ($resource::hasPage('view') && $resource::canView($record)) {
                    $breadcrumbs[
                        $resource::getUrl('view', ['record' => $record, ...$this->getRedirectUrlParameters()])
                    ] = $this->getRecordTitle();
                } elseif ($resource::hasPage('edit') && $resource::canEdit($record)) {
                    $breadcrumbs[
                        $resource::getUrl('edit', ['record' => $record, ...$this->getRedirectUrlParameters()])
                    ] = $this->getRecordTitle();
                } else {
                    $breadcrumbs[] = $this->getRecordTitle();
                }
            }
        }

        $breadcrumbs[] = $this->getBreadcrumb();

        if (filled($cluster = static::getCluster())) {
            return $cluster::unshiftClusterBreadcrumbs($breadcrumbs);
        }

        return $breadcrumbs;
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
            // 'activeRelationManager' => 0,
            'locale' => $this->activeLocale,
        ];
    }

    protected function getLivewireData(): array
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

    public function getExtraBodyAttributes(): array
    {
        return array_merge(parent::getExtraBodyAttributes(), [
            'class' => 'inspirecms-content-page',
        ]);
    }
}
