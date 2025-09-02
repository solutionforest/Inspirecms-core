<?php

namespace SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\Components;

use Filament\Forms\Components\Select;
use Filament\Support\Facades\FilamentIcon;

class TemplateThemeSelector
{
    public static function make(): Select
    {
        return Select::make('theme')
            ->label(__('inspirecms::resources/template.theme.label'))
            ->prefixIcon(FilamentIcon::resolve('inspirecms::theme'))
            ->options(static::getOptions());
    }

    public static function getOptions(): array
    {
        return collect(inspirecms_templates()->getAvailableThemes())
            ->mapWithKeys(function ($theme) {
                return [
                    $theme => $theme,
                ];
            })
            ->all();
    }
}
