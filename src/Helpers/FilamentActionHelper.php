<?php

namespace SolutionForest\InspireCms\Helpers;

use Filament\Actions\ActionGroup;

class FilamentActionHelper
{
    /**
     * @param  ActionGroup  $actionGroup
     * @return bool
     */
    public static function isAnyVisibleActionInActionGroup($actionGroup)
    {
        foreach ($actionGroup->getFlatActions() as $action) {
            if ($action->isHiddenInGroup()) {
                continue;
            }

            return false;
        }

        return true;
    }
}
