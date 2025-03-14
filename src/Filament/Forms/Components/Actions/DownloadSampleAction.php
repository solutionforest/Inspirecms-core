<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\Actions;

use Filament\Forms\Components\Actions\Action;
use Filament\Support\Facades\FilamentIcon;

class DownloadSampleAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'downloadSample';
    }

    protected function setup(): void
    {
        parent::setup();

        $this->label(__('inspirecms::buttons.download_sample.label'))
            ->icon(FilamentIcon::resolve('inspirecms::download'))
            ->button()
            ->outlined()
            ->color('warning')
            ->visible(fn (Action $action) => filled($action->getUrl()));
    }
}
