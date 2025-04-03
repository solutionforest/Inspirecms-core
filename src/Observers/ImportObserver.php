<?php

namespace SolutionForest\InspireCms\Observers;

use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Pages\Export;
use SolutionForest\InspireCms\Helpers\ImportDataHelper;
use SolutionForest\InspireCms\Helpers\UrlHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Import;

class ImportObserver
{
    /**
     * @param  Import&Model  $model
     * @return void
     */
    public function creating($model)
    {
        if (blank($model->available_at)) {
            $model->available_at = now();
        }
        if (blank($model->file_disk)) {
            $model->file_disk = ImportDataHelper::getDiskDriver();
        }
    }

    /**
     * @param  Import&Model  $model
     * @return void
     */
    public function updating($model)
    {
        if (($model->isDirty('finished_at') && ! blank($model->finished_at)) ||
            ($model->isDirty('failed_at') && ! blank($model->failed_at))) {
            $this->dispatchComplete($model);
        }
    }

    /**
     * @param  Import&Model  $model
     */
    protected function dispatchComplete($model)
    {
        event(new \SolutionForest\InspireCms\Events\Import\Completed($model->withoutRelations()));

        try {
            // Notify the user that the import job has completed
            if (($author = $model->author)) {
                $notification = $this->getImportCompletedNotification($model);
                $notification->sendToDatabase($author, true);
            }
        } catch (\Throwable $th) {
            // Do nothing
        }
    }

    /**
     * @param  Import&Model  $model
     * @return Notification
     */
    protected function getImportCompletedNotification($model)
    {
        $page = InspireCmsConfig::getFilamentPage('export', Export::class);
        $url = UrlHelper::attemptToGetUrlFromPanel($page);

        $notification = Notification::make()
            ->info()
            ->title(__('inspirecms::resources/import.notification.completed.title'))
            ->body(__('inspirecms::resources/import.notification.completed.body', ['id' => $model->getKey()]));

        if (filled($url)) {
            $notification = $notification->actions([
                Action::make('view')
                    ->label(__('inspirecms::buttons.view.label'))
                    ->button()
                    ->url($url),
            ]);
        }

        return $notification;
    }
}
