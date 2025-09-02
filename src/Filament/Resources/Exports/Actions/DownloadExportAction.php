<?php

namespace SolutionForest\InspireCms\Filament\Resources\Exports\Actions;

use Filament\Actions\Action;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\Export;

class DownloadExportAction
{
    public static function make()
    {
        return Action::make('download')
            ->label(__('inspirecms::buttons.download.label'))
            ->icon(FilamentIcon::resolve('inspirecms::download'))
            ->color('info')
            ->extraAttributes(['aria-label' => 'download'])
            ->iconButton()
            ->action(function (?Model $record) {
                if (! $record || ! $record instanceof Export) {
                    return;
                }

                [$fs, $path] = [$record->getDisk(), $record->file_name];

                return $fs->download($path);
            });
    }
}
