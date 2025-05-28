<?php

namespace SolutionForest\InspireCms\Fields\Configs\Concerns;

use Filament\Forms;

trait HasColumnsLayoutConfig
{
    public array $columnsLayout = [];

    protected static function getHasColumnsLayoutConfigComponent()
    {
        return Forms\Components\KeyValue::make('columnsLayout')
            ->label('Columns')
            ->keyLabel('Field')
            ->valueLabel('Width')
            ->keyPlaceholder('e.g. default, sm, md, lg, xl')
            ->valuePlaceholder('e.g. 1, 2, 3, 4, etc.')
            ->helperText(str('The columns for this field. Use **`default`** for the default layout, **`sm`** for small screens, **`md`** for medium screens, etc.')->markdown()->toHtmlString());
    }
}
