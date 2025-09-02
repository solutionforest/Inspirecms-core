<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Filament\Forms\Components\ModalTableSelect;
use SolutionForest\InspireCms\Base\Filament\Tables\BladeIconTable;
use SolutionForest\InspireCms\Helpers\UIHelper;

class IconPicker extends ModalTableSelect
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->tableConfiguration(BladeIconTable::class)
            ->tableArguments(fn ($state) => ['selected' => $state])
            ->getOptionLabelUsing(function ($value) {
                if (empty($value)) {
                    return null;
                }

                return UIHelper::generateTextWithIcon(
                    text: $value,
                    icon: $value,
                );
            });
    }
}
