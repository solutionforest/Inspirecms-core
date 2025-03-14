<?php

namespace SolutionForest\InspireCms\Filament\Tables\Actions;

use Filament\Support\Enums\IconPosition;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Actions\Action;

class OpenAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'open';
    }

    protected function setUp(): void
    {
        $this
            ->label(__('inspirecms::buttons.open.label'))
            ->icon(FilamentIcon::resolve('inspirecms::goto'))
            ->iconPosition(IconPosition::After)
            ->visible(fn (Action $action) => filled($action->getUrl()));
    }
}
