<?php

namespace SolutionForest\InspireCms\Filament\Resources\Templates\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class TemplateSlugColumn
{
    public static function make()
    {
        return TextColumn::make('slug')
            ->label(__('inspirecms::resources/template.slug.label'))
            ->weight('bold');
    }
}
