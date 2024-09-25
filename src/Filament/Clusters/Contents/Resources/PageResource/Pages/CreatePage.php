<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource\Pages;

use SolutionForest\InspireCms\Filament\Clusters\Contents\Concerns\ConfigureContentResourcePageSubNavigation;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Concerns\ContentPageTrait;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\Pages\BaseContentCreatePage;

class CreatePage extends BaseContentCreatePage
{
    use ConfigureContentResourcePageSubNavigation;
    use ContentPageTrait;

    public static function getResource(): string
    {
        return config('inspirecms.resources.page', PageResource::class);
    }

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();

        $record = $this->getRecord();

        if (! $record) {
            return $resource::getUrl('index');
        }

        $parent = $record?->parent;

        if ($parent) {
            return $resource::getUrl('index', ['parent' => $parent]);
        }

        return $resource::getUrl('edit', ['record' => $record]);
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        $parent = $this->getParentRecord();

        $breadcrumbs = [];

        foreach ($parent?->ancestors() as $ancestor) {
            $url = null;
            if ($resource::hasPage('view') && $resource::canView($ancestor)) {
                $url = $resource::getUrl('view', ['record' => $ancestor]);
            } elseif ($resource::hasPage('edit') && $resource::canEdit($ancestor)) {
                $url = $resource::getUrl('edit', ['record' => $ancestor]);
            }

            $parentTitle = $resource::getRecordTitle($ancestor) ?? $ancestor->getKey();

            if ($url) {
                $breadcrumbs[$url] = $parentTitle;
            } else {
                $breadcrumbs[] = $parentTitle;
            }
        }

        $breadcrumbs[] = $this->getBreadcrumb();

        if (filled($cluster = static::getCluster())) {
            return $cluster::unshiftClusterBreadcrumbs($breadcrumbs);
        }

        return $breadcrumbs;
    }

    public function getParentTitle(): ?string
    {
        $title = null;
        if ($parent = $this->getRecord()?->parent) {
            $title = static::getResource()::getRecordTitle($parent);
        }

        return $title;
    }
}
