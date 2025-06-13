<?php

namespace SolutionForest\InspireCms\Base\Filament\Concerns;

use Closure;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord\Concerns\Translatable;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseContentCreatePage;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\CreateContentRecord\Concerns\Translatable as CmsCreateContentRecordsTranslatable;
use SolutionForest\InspireCms\Filament\Resources\ContentResource;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use Throwable;

use function Filament\Support\is_app_url;

trait ContentFormTrait
{
    protected ?string $publishOperation = null;

    public function updatedActiveLocale(string $newActiveLocale): void
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

    protected function fillForm(): void
    {
        // apply the default locale if the active locale is not set
        // $this->activeLocale = $this->getDefaultTranslatableLocale();
        $this->activeLocale ??= $this->getDefaultTranslatableLocale();

        $record = $this->getRecord();
        $translatableAttributes = static::getResource()::getTranslatableAttributes();

        foreach ($this->getTranslatableLocales() as $locale) {
            $translatedData = [];

            foreach ($translatableAttributes as $attribute) {
                $translatedData[$attribute] = $record->getTranslation($attribute, $locale, useFallbackLocale: false);
            }

            if ($locale !== $this->activeLocale) {
                $this->otherLocaleData[$locale] = $this->mutateFormDataBeforeFill($translatedData);

                continue;
            }

            /** @internal Read the DocBlock above the following method. */
            $this->fillFormWithDataAndCallHooks($record, $translatedData);
        }
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

    public function publish(array $publishableData, Action $action, bool $withDescendants = false): void
    {
        $isCreating = $this->isCreatingPublishableData();

        $shouldRedirect = true;

        $publishableState = 'publish';

        $this->authorizeAccess();

        $isSuccess = $this->handlePublishableRecord(function () use ($publishableData, $isCreating, $publishableState) {

            $data = $this->getPublishableFormDataBeforePublish();

            $this->handlePublishableRecordCreateOrUpdate($data, $publishableData, $isCreating, $publishableState);

        });

        if (! $isSuccess) {
            return;
        } elseif ($withDescendants) {
            /**
             * @var Content & Model $parent
             */
            $parent = $this->getRecord();

            $parent->descendants->each(
                fn (Content | Model $descendant) => $this->wrapPublisableSavingEventIntoDbTransaction(function () use ($descendant, $publishableData, $publishableState) {
                    $descendant->setPublishableData($publishableData);
                    $descendant->setPublishableState($publishableState);
                    $descendant->save();
                })
            );
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

    public function handlePublishableRecord(Closure $callback)
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

            /** @var Model & Content */
            $record = app(static::getModel());

            $isLivewireHandleTranslatable = collect(class_uses_recursive($this))
                ->where(fn ($traitClass) => in_array($traitClass, [
                    Translatable::class,
                    CmsCreateContentRecordsTranslatable::class,
                ]))
                ->isNotEmpty();
            if ($isLivewireHandleTranslatable) {
                $translatableAttributes = static::getResource()::getTranslatableAttributes();
                $record->fill(Arr::except($data, $translatableAttributes));
                foreach (Arr::only($data, $translatableAttributes) as $key => $value) {
                    $record->setTranslation($key, $this->activeLocale, $value);
                }
            } else {
                $record->fill($data);
            }
            // region Handle Record Creating

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
            // endregion Handle Record Creating

            $this->{$formName}->model($this->getRecord())->saveRelationships();

            $this->callHook('afterCreate');

        } else {

            // region Handle Record Updating
            /** @var Model&Content */
            $record = $this->getRecord();

            $record->setPublishableData($publishableData);

            $record->setPublishableState($publishableAction);

            $this->record = $this->handleRecordUpdate($record, $data);
            // endregion Handle Record Updating

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

    protected function onValidationError(ValidationException $exception): void
    {
        // Display notification if have "Unique" validation error for "slug" field
        if (collect($exception->validator->failed()['data.slug']['Unique'] ?? [])->isNotEmpty()) {
            Notification::make()
                ->title(__('inspirecms::resources/content.notification.remove_content_same_slug_in_same_parent.title'))
                ->body(__('inspirecms::resources/content.notification.remove_content_same_slug_in_same_parent.body'))
                ->warning()
                ->send();
        }
    }

    // region Help functions
    protected function getPublishFormAction(string $operation, string $model): Action
    {
        if (is_null($operation) || $operation === 'create') {
            $this->publishOperation = 'create';
        } else {
            $this->publishOperation = 'edit';
        }

        return Action::make('publish')
            ->label(__('inspirecms::buttons.publish.label'))
            ->modalHeading(__('inspirecms::buttons.publish.heading'))
            ->modalSubmitActionLabel(__('inspirecms::buttons.publish.label'))
            ->successNotificationTitle(__('inspirecms::buttons.publish.messages.success.title'))
            ->keyBindings(['mod+p'])
            ->color('primary')
            ->form(function (Form $form) {
                $resource = InspireCmsConfig::getFilamentResource('content', ContentResource::class);
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
            // Cannot publish if the parent is not published
            ->disabled(function (?Model $record, $livewire) {
                // Create page
                if ($livewire instanceof BaseContentCreatePage) {

                    $parent = $livewire->getParentRecord();

                    return static::isReadyForPublication($parent);
                }

                // Edit page
                if ($record === null || ($record && ! $record->exists) || ! $record instanceof Content || $record->isRootLevel()) {
                    return false;
                }

                return static::isReadyForPublication($record->parent);
            });
    }

    protected static function isReadyForPublication(?Model $parent): bool
    {
        if ($parent === null || ($parent && ! $parent->exists) || ! $parent instanceof Content) {
            return false;
        }

        return ! $parent->isPublished();
    }

    protected function getPublishableFormName(): string
    {
        return 'form';
    }

    protected function isCreatingPublishableData(): bool
    {
        return $this->publishOperation !== 'edit';
    }

    protected function wrapPublisableSavingEventIntoDbTransaction(Closure $callback)
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
    // endregion Help functions
}
