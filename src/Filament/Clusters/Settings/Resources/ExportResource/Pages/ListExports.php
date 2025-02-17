<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\ExportResource\Pages;

use Filament\Actions;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListPage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\ExportResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class ListExports extends BaseListPage
{
    public function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->createAnother(false)
                ->modalWidth('lg')
                ->stickyModalHeader()->stickyModalHeader()
                ->slideOver()
                // todo: add translations
                ->label('Export')
                ->modalSubmitActionLabel('Export')
                ->successNotificationTitle('Queued for export, please wait for the download link.')
                ->using(function ($action, $data, $model) {
                    
                    $user = auth()->user();
                    $exporter = $data['exporter'] ?? null;

                    if (! $user || ! filled($exporter)) {
                        return $action->cancel();
                    }
                    $export = app($model);
                    $export->author()->associate($user);
                    $export->exporter = $exporter;
                    $export->save();

                    return $export;
                }),
        ];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('export', ExportResource::class);
    }
}
