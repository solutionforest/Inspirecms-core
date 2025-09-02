<?php

namespace SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\Components;

use SolutionForest\InspireCms\Filament\Forms\Components\CodeEditor;

class TemplateContentEditor
{
    public static function make($name = 'content'): CodeEditor
    {
        return CodeEditor::make($name)
            ->minHeight('48rem');
    }
}
