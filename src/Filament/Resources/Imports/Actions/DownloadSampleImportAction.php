<?php

namespace SolutionForest\InspireCms\Filament\Resources\Imports\Actions;

use Filament\Actions\Action;
use Filament\Support\Facades\FilamentIcon;
use SolutionForest\InspireCms\Services\ImportServiceInterface;
use Throwable;

class DownloadSampleImportAction
{
    public static function make()
    {
        return Action::make('download_sample')
            ->label(__('inspirecms::buttons.download_sample.label'))
            ->icon(FilamentIcon::resolve('inspirecms::download'))
            ->button()
            ->outlined()
            ->color('warning')
            ->failureNotificationTitle('Download Failed')
            ->action(function (Action $action) {
                try {
                    $importService = app(ImportServiceInterface::class);

                    $file = $importService->buildSampleZip();

                    return response()
                        ->download($file, 'import-sample-' . uniqid() . '.zip')
                        ->deleteFileAfterSend(true);
                } catch (Throwable $th) {
                    $action->failure();
                }
            });
    }
}
