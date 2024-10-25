<?php

namespace SolutionForest\InspireCms\Base\Enums\Interfaces;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

interface NavigationCategory extends HasLabel, HasColor
{
    public static function getDefaultValue(): NavigationCategory;
}
