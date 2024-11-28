<?php

namespace SolutionForest\InspireCms\Observers;

use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Events\ImportJob\ImportJobCompleted;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Models\Contracts\ImportJob;

class ImportJobObserver
{
    /**
     * @param  ImportJob&Model  $model
     * @return void
     */
    public function creating($model)
    {
        if (blank($model->available_at)) {
            $model->available_at = now();
        }
        if (blank($model->disk)) {
            $model->disk = $model->getDiskDriver();
        }
    }

    /**
     * @param  ImportJob&Model  $model
     * @return void
     */
    public function updating($model)
    {
        if (($model->isDirty('finished_at') && ! blank($model->finished_at)) ||
            ($model->isDirty('failed_at') && ! blank($model->failed_at))) 
        {
            $this->dispatchComplete($model);
        }
    }

    /**
     * @param  ImportJob&Model  $model
     */
    protected function dispatchComplete($model)
    {
        event(new ImportJobCompleted($model->withoutRelations()));
        
        try {
            // Notify the user that the import job has completed
            if (($author = $model->author)){
                $notification = $this->getImportJobCompletedNotification($model);
                $notification->sendToDatabase($author, true);
            }
        } catch (\Throwable $th) {
            // Do nothing
        }
    }

    /**
     * @param  ImportJob&Model  $model
     * 
     * @return Notification
     */
    protected function getImportJobCompletedNotification($model)
    {
        $url = InspireCms::getImportDataUrl();

        $notification = Notification::make()
            ->info()
            ->title(__('inspirecms::resources/import-jobs.notification.completed.title'))
            ->body(__('inspirecms::resources/import-jobs.notification.completed.body', ['id' => $model->getKey()]));

        if (filled($url)) {
            $notification = $notification->actions([
                Action::make('view')->button()->url($url),
            ]); 
        }

        return $notification;
    }
}
