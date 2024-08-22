<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\Actions;

use Filament\Forms\Components\Actions\Action;

class ResetAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'reset';
    }

    protected function setup(): void
    {
        parent::setup();

        $this->icon('heroicon-o-arrow-path')
            ->action(fn ($component) => $component->state(''))
            ->disabled(fn ($component) => $component->isDisabled());
    }
}
