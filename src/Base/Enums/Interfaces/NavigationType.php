<?php

namespace SolutionForest\InspireCms\Base\Enums\Interfaces;

use Filament\Support\Contracts\HasLabel;

interface NavigationType extends HasLabel
{
    public static function getDefaultValue(): NavigationType;

    public function canEditIsVisible(): bool;
}
