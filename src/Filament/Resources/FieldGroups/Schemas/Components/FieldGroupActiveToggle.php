<?php

namespace SolutionForest\InspireCms\Filament\Resources\FieldGroups\Schemas\Components;

use Filament\Forms\Components\Hidden;

class FieldGroupActiveToggle
{
    public static function make(): Hidden
    {
        return Hidden::make('active')
            ->dehydratedWhenHidden(true)
            ->dehydrateStateUsing(fn () => true);
    }
}
