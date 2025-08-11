<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\Actions;

use Filament\Actions\Action;
use Filament\Support\Facades\FilamentIcon;

class ResetAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'reset';
    }

    protected function setup(): void
    {
        parent::setup();

        $this->icon(FilamentIcon::resolve('inspirecms::reset'))
            ->action(fn ($component) => $component->state(''))
            ->disabled(fn ($component) => $component->isDisabled());
    }
}
