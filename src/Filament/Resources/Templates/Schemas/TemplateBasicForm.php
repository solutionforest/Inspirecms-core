<?php

namespace SolutionForest\InspireCms\Filament\Resources\Templates\Schemas;

use Filament\Schemas\Schema;
use SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\Components\TemplateSlugInput;

class TemplateBasicForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TemplateSlugInput::make(),
            ]);
    }
}
