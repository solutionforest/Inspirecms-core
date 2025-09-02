<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\Schemas;

use Filament\Schemas\Schema;
use SolutionForest\InspireCms\Filament\Resources\Contents\Schemas\Components\ContentPublishedAtDateTimePicker;

class PublishContentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ContentPublishedAtDateTimePicker::make(),
            ]);
    }
}
