<?php

namespace SolutionForest\InspireCms\Observers;

use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Helpers\ExportDataHelper;
use SolutionForest\InspireCms\Models\Contracts\Export;

class ExportObserver
{
    /**
     * @param  Export&Model  $model
     * @return void
     */
    public function creating($model)
    {
        if (blank($model->file_disk)) {
            $model->file_disk = ExportDataHelper::getDiskDriver();
        }
    }

    /**
     * @param  Export&Model  $model
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
     * @param  Export&Model  $model
     */
    protected function dispatchComplete($model)
    {
        event(new \SolutionForest\InspireCms\Events\Export\Completed($model->withoutRelations()));

        try {
            // Notify the user that the import job has completed
            if (($author = $model->author)) {
                $notification = $this->getCompletedNotification($model);
                $notification->sendToDatabase($author, true);
            }
        } catch (\Throwable $th) {
            // Do nothing
        }
    }

    /**
     * @param  Export&Model  $model
     * @return Notification
     */
    protected function getCompletedNotification($model)
    {
        return Notification::make()
            ->info()
            ->title(__('inspirecms::resources/export.notification.completed.title'))
            ->body(__('inspirecms::resources/export.notification.completed.body', ['id' => $model->getKey()]))
            ->actions([
                Action::make('view')
                    ->label(__('inspirecms::actions.view.label'))
                    ->url(function () use ($model) {
                        return route('cms.export.show', ['id' => $model->getKey()]);
                    }),
            ]);
    }
}
