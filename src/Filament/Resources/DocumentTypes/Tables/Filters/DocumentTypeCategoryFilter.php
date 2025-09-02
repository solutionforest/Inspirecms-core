<?php

namespace SolutionForest\InspireCms\Filament\Resources\DocumentTypes\Tables\Filters;

use Filament\Tables\Filters\SelectFilter;
use SolutionForest\InspireCms\InspireCmsConfig;

class DocumentTypeCategoryFilter
{
    public static function make()
    {
        return SelectFilter::make('category')
            ->multiple()
            ->label(__('inspirecms::resources/document-type.category.label'))
            ->options(InspireCmsConfig::getDocumentTypeModelClass()::getCategoryEnumClass());
    }
}
