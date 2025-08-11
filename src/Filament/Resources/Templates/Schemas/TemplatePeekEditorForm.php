<?php

namespace SolutionForest\InspireCms\Filament\Resources\Templates\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\Components\TemplateContentEditor;
use SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\Components\TemplatePageComponentInstructionsEntry;
use SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\Components\TemplatePropertyTypeInstructionsEntry;
use SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\Components\TemplateThemeSelector;

class TemplatePeekEditorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('record_id'),
                Hidden::make('document_type_id'),
                TemplateThemeSelector::make()->disabled(),
                Tabs::make()
                    ->tabs([
                        Tab::make(__('inspirecms::resources/template.editor.tabs.content'))
                            ->schema([
                                TemplatePageComponentInstructionsEntry::make(),
                                TemplateContentEditor::make('html_content')->hiddenLabel(),
                            ]),
                        Tab::make(__('inspirecms::resources/template.editor.tabs.instructions'))
                            ->schema([
                                TemplatePropertyTypeInstructionsEntry::make(),
                            ]),
                    ]),
            ]);
    }
}
