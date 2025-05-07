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

    public static function isHtmlString($icon): bool
    {
        if (is_null($icon)) {
            return false;
        }

        if (is_string($icon)) {
            // Check if the string contains HTML tags
            return str_contains($icon, '<') && str_contains($icon, '>');
        }

        return $icon instanceof \Illuminate\Contracts\Support\Htmlable;
    }
}
