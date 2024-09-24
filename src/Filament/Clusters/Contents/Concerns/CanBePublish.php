<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Contents\Concerns;

use Closure;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\BaseContentResource;
use SolutionForest\InspireCms\Models\Contracts\Content as CmsContent;
use Throwable;

use function Filament\Support\is_app_url;

trait CanBePublish
{
    protected ?string $publishOperation = null;

    protected function getPublishFormAction(string $operation, string $model): Action
    {
        if (is_null($operation) || $operation === 'create') {
            $this->publishOperation = 'create';
        } else {
            $this->publishOperation = 'edit';
        }

        return Action::make('publish')
            ->label(__('inspirecms::actions.publish.label'))
            ->modalSubmitActionLabel(__('inspirecms::actions.publish.actions.publish.label'))
            ->keyBindings(['mod+p'])
            ->color('primary')
            ->form(
                fn (Form $form) => $form
                    ->schema([
                        BaseContentResource::getPublishedAtComponent(),
                    ])
                    ->operation('publish')
            )
            ->beforeFormValidated(function (Action $action) {
                try {

                    $this->validatePublishableData();

                } catch (Throwable $e) {
                    Notification::make()
                        ->title(__('inspirecms::notification.form_check_error.title'))
                        ->danger()
                        ->send();

                    throw $e;
                }
            })
            ->action($this->publish())
            ->model($model)
            ->authorize('publish')
            ->successNotification($this->getPublishedNotification());
    }

    public function publish(): \Closure
    {
        return function (array $data, Action $action) {

            $isCreating = $this->isCreatingPublishableData();

            $shouldRedirect = true;

            $this->authorizeAccess();

            $isSuccess = $this->handlePublishableRecord(function () use ($data, $isCreating) {

                $data = $this->getPublishableFormDataBeforePublish($data);

                $this->handlePublishableRecordCreateOrUpdate($data, $isCreating, 'publish');

            });

            if (! $isSuccess) {
                return;
            }

            $action->success();

            if ($isCreating) {

                $redirectUrl = $this->getRedirectUrl();

                $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));

            } else {

                if ($shouldRedirect && ($redirectUrl = $this->getRedirectUrl())) {
                    $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
                }

            }
        };
    }

    /**
     * Handles the publishable record by executing the provided callback.
     *
     * @param  \Closure  $callback  The callback function to handle the publishable record.
     */
    public function handlePublishableRecord(\Closure $callback)
    {
        $isSuccess = $this->wrapPublisableSavingEventIntoDbTransaction($callback);

        if (! $isSuccess) {
            return false;
        }

        $this->rememberData();

        return true;
    }

    public function handlePublishableRecordCreateOrUpdate(array $data, bool $isCreating, string $publishableAction = 'draft'): Model
    {
        if ($isCreating) {

            //region Handle Record Creating
            /** @var Model|CmsContent */
            $record = new ($this->getModel())($data);

            $record->setPublishableState($publishableAction);

            if (
                static::getResource()::isScopedToTenant() &&
                ($tenant = Filament::getTenant())
            ) {
                return $this->associateRecordWithTenant($record, $tenant);
            }

            $record->save();

            $this->record = $record;
            //endregion Handle Record Creating

            $this->form->model($this->getRecord())->saveRelationships();

            $this->callHook('afterCreate');

        } else {

            //region Handle Record Updating
            /** @var Model|CmsContent */
            $record = $this->getRecord();

            $record->setPublishableState($publishableAction);

            $this->record = $this->handleRecordUpdate($record, $data);
            //endregion Handle Record Updating

            // Skip save relationships on `getPublishableFormDataBeforePublish`, and handle on this line
            $this->form->model($this->getRecord())->saveRelationships();

            $this->callHook('afterSave');
        }

        return $this->getRecord();
    }

    protected function isCreatingPublishableData(): bool
    {
        return $this->publishOperation !== 'edit';
    }

    public function getPublishableFormDataBeforePublish(array $extraData): array
    {
        if ($this->isCreatingPublishableData()) {

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeCreate(array_merge($data, $extraData));

            $this->callHook('beforeCreate');

        } else {

            $this->callHook('beforeValidate');

            // Avoid save relationships before update
            $data = $this->form->getState(shouldCallHooksBefore: false, afterValidate: null);

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeSave(array_merge($data, $extraData));

            $this->callHook('beforeSave');

        }

        return $data;
    }

    public function validatePublishableData(): void
    {
        $this->form->validate();
    }

    //region Notification
    protected function getPublishedNotification(): ?Notification
    {
        $title = $this->getPublishedNotificationTitle();

        if (blank($title)) {
            return null;
        }

        return Notification::make()
            ->success()
            ->title($title);
    }

    protected function getPublishedNotificationTitle(): ?string
    {
        return __('inspirecms::actions.publish.notifications.published.title');
    }
    //endregion Notification

    //region Help functions
    protected function wrapPublisableSavingEventIntoDbTransaction(\Closure $callback)
    {

        try {
            $this->beginDatabaseTransaction();

            $callback();

            $this->commitDatabaseTransaction();

        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return false;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        return true;
    }
    //endregion Help functions
}
