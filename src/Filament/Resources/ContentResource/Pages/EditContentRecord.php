<?php

namespace SolutionForest\InspireCms\Filament\Resources\ContentResource\Pages;

use Filament\Resources\Pages\Enums\ContentTabPosition;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseContentEditPage;
use SolutionForest\InspireCms\Filament\Resources\ContentResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class EditContentRecord extends BaseContentEditPage
{
    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('content', ContentResource::class);
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        if (empty($this->getRelationManagers())) {
            return false;
        }

        return true;
    }

    public function getContentTabPosition(): ?ContentTabPosition
    {
        return ContentTabPosition::After;
    }
}
