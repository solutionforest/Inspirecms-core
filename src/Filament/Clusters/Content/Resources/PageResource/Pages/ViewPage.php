<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource\Pages;

use Filament\Resources\Pages\ContentTabPosition;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages\BaseContentViewPage;
use SolutionForest\InspireCms\InspireCmsConfig;

class ViewPage extends BaseContentViewPage
{
    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('page', PageResource::class);
    }

    public function getDocumentType(): int | string | Model
    {
        return $this->getRecord()->documentType;
    }

    public function getParent(): string | int | Model | null
    {
        return $this->getRecord()->parent;
    }

    public function getParentKey(): string | int | null
    {
        return $this->getRecord()->parent_id;
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabPosition(): ?ContentTabPosition
    {
        return ContentTabPosition::After;
    }
}
