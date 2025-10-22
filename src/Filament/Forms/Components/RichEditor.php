<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Filament\Forms\Components\RichEditor as BaseRichEditor;
use SolutionForest\InspireCms\Filament\Forms\Components\RichEditor\Plugins\ContentPickerRichPlugin;
use SolutionForest\InspireCms\Filament\Forms\Components\RichEditor\Plugins\MediaPickerRichPlugin;

class RichEditor extends BaseRichEditor
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->plugins([
            ContentPickerRichPlugin::make(),
            MediaPickerRichPlugin::make(),
        ]);
    }
}
