<?php

namespace SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\InspireCmsConfig;

class TemplateSlugInput
{
    public static function make(): TextInput
    {
        return TextInput::make('slug')
            ->label(__('inspirecms::resources/template.slug.label'))
            ->validationAttribute(__('inspirecms::resources/template.slug.validation_attribute'))
            ->inlineLabel()
            ->required()
            ->maxLength(255)
            ->live(true, 500)
            ->afterStateUpdated(fn ($component, ?string $state) => $component->state(Str::slug($state)))
            ->unique(
                table: InspireCmsConfig::getTemplateModelClass(),
                column: 'slug',
                ignoreRecord: true
            );
    }
}
