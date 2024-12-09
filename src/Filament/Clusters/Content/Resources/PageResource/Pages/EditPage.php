<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource\Pages;

use Filament\Resources\Pages\ContentTabPosition;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages\BaseContentEditPage;
use SolutionForest\InspireCms\InspireCmsConfig;

class EditPage extends BaseContentEditPage
{
    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('page', PageResource::class);
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
