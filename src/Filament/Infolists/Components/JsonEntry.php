<?php

namespace SolutionForest\InspireCms\Filament\Infolists\Components;

use Filament\Infolists\Components\Entry;

class JsonEntry extends Entry
{
    protected string $view = 'inspirecms::filament.infolists.components.json-entry';

    public function getState(): mixed
    {
        $state = parent::getState();

        if (is_array($state) || is_object($state)) {
            return json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        return $state;
    }
}
