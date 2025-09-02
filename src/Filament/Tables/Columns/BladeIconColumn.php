<?php

namespace SolutionForest\InspireCms\Filament\Tables\Columns;

use BackedEnum;
use Filament\Tables\Columns\IconColumn;

class BladeIconColumn extends IconColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->boolean(false)
            ->alignCenter()
            ->verticallyAlignCenter();
    }

    public function getIcon(mixed $state): string | BackedEnum | null
    {
        $icon = $this->evaluate($this->icon, [
            'state' => $state,
        ]);

        if (is_string($state)) {
            $icon ??= $state;
        }

        try {
            if (is_string($icon) && svg($icon) instanceof \BladeUI\Icons\Svg) {
                return $icon;
            }
        } catch (\Throwable $th) {
            // Skip
        }

        return parent::getIcon($state);
    }
}
