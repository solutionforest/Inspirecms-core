<?php

namespace SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

class ThemeInput
{
    public static function make(): TextInput
    {
        return TextInput::make('theme')
            ->label(__('inspirecms::resources/template.theme.label'))
            ->inlineLabel()
            ->required()
            ->live(true, 500)
            ->afterStateUpdated(fn ($component, ?string $state) => $component->state(Str::slug($state)));
    }
}
