<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource\Pages;

use Filament\Actions;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Concerns\ConfigureContentResourcePageSubNavigation;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Contracts\HasPublishForm;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\Pages\BaseContentViewPage;

class ViewPage extends BaseContentViewPage implements HasPublishForm
{
    use ConfigureContentResourcePageSubNavigation;
    
    public static function getResource(): string
    {
        return config('inspirecms.resources.page', PageResource::class);
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        $record = $this->getRecord();

        $ancestors = $record->ancestors();

        $breadcrumbs = [];

        foreach ($ancestors as $ancestor) {
            $breadcrumbs[$resource::getUrl('index', ['parent' => $ancestor->getKey()]) ?? $resource::getBreadcrumb()] = $resource::getRecordTitle($ancestor);
        }

        if ($record->exists && $resource::hasRecordTitle()) {
            if ($resource::hasPage('view') && $resource::canView($record)) {
                $breadcrumbs[
                    $resource::getUrl('view', ['record' => $record])
                ] = $this->getRecordTitle();
            } elseif ($resource::hasPage('edit') && $resource::canEdit($record)) {
                $breadcrumbs[
                    $resource::getUrl('edit', ['record' => $record])
                ] = $this->getRecordTitle();
            } else {
                $breadcrumbs[] = $this->getRecordTitle();
            }
        }

        $breadcrumbs[] = $this->getBreadcrumb();

        if (filled($cluster = static::getCluster())) {
            return $cluster::unshiftClusterBreadcrumbs($breadcrumbs);
        }

        return $breadcrumbs;
    }
    
    protected function configureDeleteAction(Actions\DeleteAction $action): void
    {
        parent::configureDeleteAction($action);

        $parent = $this->getRecord()?->parent;

        $url = $parent
            ? static::getResource()::getUrl('index', ['parent' => $parent])
            : ($this->getCluster() ? $this->getCluster()::getUrl() : static::getResource()::getUrl('index'));

        $action
            ->successRedirectUrl($url);
    }
}
