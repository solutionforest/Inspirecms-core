<?php

namespace SolutionForest\InspireCms\Filament\Resources\Templates\Actions;

use Filament\Actions\SelectAction;
use Filament\Support\Facades\FilamentIcon;
use SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\Components\TemplateThemeSelector;

class ThemeSelector
{
    public static function make(): SelectAction
    {
        return SelectAction::make('theme')
            ->options(TemplateThemeSelector::getOptions())
            ->view('inspirecms::filament.actions.select-action', [
                'icon' => FilamentIcon::resolve('inspirecms::theme'),
            ]);
    }
}
