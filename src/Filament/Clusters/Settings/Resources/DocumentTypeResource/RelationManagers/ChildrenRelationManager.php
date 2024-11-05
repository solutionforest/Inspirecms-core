<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\RelationManagers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Base\Filament\RelationManagers\BaseContentChildrenRelationManager;

class ChildrenRelationManager extends BaseContentChildrenRelationManager
{
    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        if (is_null($ownerRecord->children_count)) {
            $ownerRecord->loadCount('children');
        }

        return $ownerRecord->children_count;
    }

    protected function isRedirectToDetailPage(): bool
    {
        return true;
    }
}
