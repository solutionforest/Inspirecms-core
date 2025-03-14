<?php

namespace SolutionForest\InspireCms\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Facades\FilamentIcon;

class TrashBinAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'trashBin';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('inspirecms::buttons.trash_bin.label'))
            ->color('gray')
            ->icon(FilamentIcon::resolve('inspirecms::recycle_bin'))
            ->visible(fn (Action $action) => filled($action->getUrl()));
    }
}
