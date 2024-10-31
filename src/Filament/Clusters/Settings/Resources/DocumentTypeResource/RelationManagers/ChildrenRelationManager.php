<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\RelationManagers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Base\Filament\RelationManagers\BaseContentChildrenRelationManager;

class ChildrenRelationManager extends BaseContentChildrenRelationManager
{
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        if (! parent::canViewForRecord($ownerRecord, $pageClass)) {
            return false;
        }

        return $ownerRecord->isRoot();
    }
}
