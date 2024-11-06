<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Concerns;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use Throwable;

use function Filament\Support\is_app_url;

trait ContentFormTrait
{
    protected ?string $publishOperation = null;

    public function updatedActiveLocaleForContent(string $newActiveLocale): void
    {
        if (blank($this->oldActiveLocale)) {
            return;
        }

        $this->resetValidation();

        $translatableAttributes = $this->getTranslatableAttributes();

        $this->otherLocaleData[$this->oldActiveLocale] = Arr::only($this->data, $translatableAttributes);

        $this->data = $this->mutuateDataWhileUpdatedActiveLocale(
            Arr::except($this->data, $translatableAttributes),
            $this->otherLocaleData[$this->activeLocale] ?? []
        );

        unset($this->otherLocaleData[$this->activeLocale]);
    }

    /**
     * Get the list of translatable attributes.
     *
     * @param  bool  $exceptPropertyData  Whether to exclude property data from the attributes.
     * @return array The list of translatable attributes.
     */
    protected function getTranslatableAttributes(bool $exceptPropertyData = true): array
    {
        $translatableAttributes = static::getResource()::getTranslatableAttributes();

        if (! $exceptPropertyData) {
            return $translatableAttributes;
        }

        // Filter out the propertyDataTranslation
        return Arr::where($translatableAttributes, fn ($attribute) => $attribute != 'propertyData');
    }

    protected function mutuateDataWhileUpdatedActiveLocale(array $data, array $otherLocaleData, bool $exceptPropertyData = true): array
    {
        foreach ($otherLocaleData as $key => $value) {

            if ($exceptPropertyData && $key === 'propertyData') {
                continue;
            }

            $data[$key] = $value;

        }

        return $data;
    }

    public function validatePublishableData(): void
    {
        $this->{$this->getPublishableFormName()}->validate();
    }

    public function publish(array $publishableData, Action $action)
    {
        $isCreating = $this->isCreatingPublishableData();

        $shouldRedirect = true;

        $this->authorizeAccess();

        $isSuccess = $this->handlePublishableRecord(function () use ($publishableData, $isCreating) {

            $data = $this->getPublishableFormDataBeforePublish();

            $this->handlePublishableRecordCreateOrUpdate($data, $publishableData, $isCreating, 'publish');

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
    }

    public function handlePublishableRecord(\Closure $callback)
    {
        $isSuccess = $this->wrapPublisableSavingEventIntoDbTransaction($callback);

        if (! $isSuccess) {
            return false;
        }

        $this->rememberData();

        return true;
    }

    public function handlePublishableRecordCreateOrUpdate(array $data, array $publishableData, bool $isCreating, string $publishableAction = 'draft'): Model
    {
        $formName = $this->getPublishableFormName();

        if ($isCreating) {

            /** @var Model|\SolutionForest\InspireCms\Models\Contracts\Content */
            $record = app(static::getModel());

            if (in_array(\Filament\Resources\Pages\CreateRecord\Concerns\Translatable::class, class_uses_recursive($this))) {
                $translatableAttributes = static::getResource()::getTranslatableAttributes();
                $record->fill(Arr::except($data, $translatableAttributes));
                foreach (Arr::only($data, $translatableAttributes) as $key => $value) {
                    $record->setTranslation($key, $this->activeLocale, $value);
                }
            } else {
                $record->fill($data);
            }
            //region Handle Record Creating

            $record->setPublishableData($publishableData);

            $record->setPublishableState($publishableAction);

            if ($this instanceof \Filament\Resources\Pages\Page) {
                if (
                    static::getResource()::isScopedToTenant() &&
                    ($tenant = Filament::getTenant())
                ) {
                    return $this->associateRecordWithTenant($record, $tenant);
                }
            }

            $record->save();

            $this->record = $record;
            //endregion Handle Record Creating

            $this->{$formName}->model($this->getRecord())->saveRelationships();

            $this->callHook('afterCreate');

        } else {

            //region Handle Record Updating
            /** @var Model|\SolutionForest\InspireCms\Models\Contracts\Content */
            $record = $this->getRecord();

            $record->setPublishableData($publishableData);

            $record->setPublishableState($publishableAction);

            $this->record = $this->handleRecordUpdate($record, $data);
            //endregion Handle Record Updating

            // Skip save relationships on `getPublishableFormDataBeforePublish`, and handle on this line
            $this->{$formName}->model($this->getRecord())->saveRelationships();

            $this->callHook('afterSave');
        }

        return $this->getRecord();
    }

    public function getPublishableFormDataBeforePublish(): array
    {
        $formName = $this->getPublishableFormName();

        if ($this->isCreatingPublishableData()) {

            $this->callHook('beforeValidate');

            $data = $this->{$formName}->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeCreate($data);

            $this->callHook('beforeCreate');

        } else {

            $this->callHook('beforeValidate');

            // Avoid save relationships before update
            $data = $this->{$formName}->getState(shouldCallHooksBefore: false, afterValidate: null);

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeSave($data);

            $this->callHook('beforeSave');

        }

        return $data;
    }

    //region Notification
    protected function getPublishedNotification(): ?Notification
    {
        $title = __('inspirecms::actions.publish.notifications.published.title');

        if (blank($title)) {
            return null;
        }

        return Notification::make()
            ->success()
            ->title($title);
    }
    //endregion Notification

    //region Help functions
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
            ->form(function (Form $form) {
                $resource = config('inspirecms::resources.page', PageResource::class);
                if (! method_exists($resource, 'getPublishedAtFormComponent')) {
                    throw new \RuntimeException('The resource must have a getPublishedAtFormComponent method.');
                }

                return $form
                    ->schema([
                        $resource::getPublishedAtFormComponent(),
                    ])
                    ->operation('publish');
            })
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
            ->action(fn ($data, $action) => $this->publish($data, $action))
            ->model($model)
            ->authorize('publish')
            ->successNotification($this->getPublishedNotification());
    }

    protected function getPublishableFormName(): string
    {
        return 'form';
    }

    protected function isCreatingPublishableData(): bool
    {
        return $this->publishOperation !== 'edit';
    }

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
