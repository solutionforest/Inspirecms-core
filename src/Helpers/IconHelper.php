<?php

namespace SolutionForest\InspireCms\Helpers;

class IconHelper
{
    public static function isCmsCustomIcon($icon): bool
    {
        if ($icon && filled($icon) && is_string($icon)) {
            return str_starts_with($icon, 'inspirecms::');
        }
        return false;
    }
}
