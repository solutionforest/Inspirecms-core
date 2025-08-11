<?php

namespace SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\Components;

use Filament\Infolists\Components\CodeEntry;
use SolutionForest\InspireCms\Helpers\TemplateHelper;

class TemplatePageComponentInstructionsEntry
{
    public static function make(): CodeEntry
    {
        return CodeEntry::make('page_component_instructions')
            ->grammar('php')
            ->state(TemplateHelper::retrieveDefaultThemeContent())
            ->copyable();
    }
}
