<?php

namespace SolutionForest\InspireCms\Filament\Infolists\Components\Actions;

use Filament\Infolists\Components\Actions\Action;
use Filament\Support\Facades\FilamentIcon;

class DownloadAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'download';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('inspirecms::buttons.download.label'))
            ->icon(FilamentIcon::resolve('inspirecms::download'))
            ->color('info');
    }
}
