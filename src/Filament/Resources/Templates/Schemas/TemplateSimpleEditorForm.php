<?php

namespace SolutionForest\InspireCms\Filament\Resources\Templates\Schemas;

use Filament\Schemas\Schema;
use SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\Components\TemplateContentEditor;
use SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\Components\TemplateThemeSelector;

class TemplateSimpleEditorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TemplateThemeSelector::make()->disabled(),
                TemplateContentEditor::make()->hiddenLabel(),
            ]);
    }
}
