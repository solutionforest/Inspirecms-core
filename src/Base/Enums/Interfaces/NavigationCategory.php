<?php

namespace SolutionForest\InspireCms\Base\Enums\Interfaces;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

interface NavigationCategory extends HasColor, HasLabel
{
    public static function getDefaultValue(): NavigationCategory;
}
