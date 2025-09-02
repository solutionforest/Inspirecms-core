<?php

namespace SolutionForest\InspireCms\Filament\Resources\Templates\Tables\Columns;

use Filament\Tables\Columns\IconColumn;

class TemplateIsDefaultColumn
{
    public static function make()
    {
        return IconColumn::make('is_default')
            ->label(__('inspirecms::resources/template.is_default.label'))
            ->boolean();
    }
}
