<?php

namespace SolutionForest\InspireCms\Base\Enums\Interfaces;

use Filament\Support\Contracts\HasLabel;

interface NavigationCategory extends HasLabel
{
    public static function getDefaultValue(): NavigationCategory;
}
