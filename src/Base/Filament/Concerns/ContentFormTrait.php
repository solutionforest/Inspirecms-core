<?php

namespace SolutionForest\InspireCms\Base\Filament\Concerns;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseContentCreatePage;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\CreateContentRecord\Concerns\Translatable as CmsCreateContentRecordsTranslatable;
use SolutionForest\InspireCms\Filament\Resources\Contents\Schemas\PublishContentForm;
use SolutionForest\InspireCms\Helpers\DiffHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use Throwable;

use function Filament\Support\is_app_url;

trait ContentFormTrait
{
    use HasContentFormActions;

    protected ?string $publishOperation = null;

    /**
     * Called by Filament's cacheTraitActions() during bootedInteractsWithActions(),
     * BEFORE cacheMountedActions() resolves the mounted action. This ensures the
     * extra status form actions are in cachedActions in time for resolution.
     */
    public function cacheHasContentFormActions(): void
    {
        foreach (inspirecms_content_statuses()->getFormActions() as $action) {
            $this->cacheAction($action);
        }
    }

    public function bootedContentFormTrait(): void
    {
        $mainActions = [
            $this->publishAction(),
        ];
        if (! $this instanceof CreateRecord) {
            $mainActions[] = $this->publishDescendantsAndSelfAction();
        }
        $extraActions = inspirecms_content_statuses()->getFormActions();

        $actions = collect([
            ...$mainActions,
            ...$extraActions,
        ])
            ->each(fn (Action $action) => $this->cacheAction($action))
            ->all();

        FilamentView::registerRenderHook(
            'inspirecms_content_form_main_actions',
            function () use ($mainActions) {

                $filteredActions = collect($mainActions)
                    ->filter(fn (Action $action) => ! $action->isHidden())
                    ->all();

                if (empty($filteredActions)) {
                    return;
                }

                if (count($filteredActions) === 1) {
                    return collect($filteredActions)->map(fn (Action $action) => $action->toHtml())->implode('');
                }

                return ActionGroup::make([])
                    ->label(__('inspirecms::buttons.publish.label'))
                    ->button()
                    ->color('primary')
                    ->dropdownPlacement('top-end')
                    ->actions(
                        collect($filteredActions)
                            ->map(fn (Action $action) => $action->grouped())
                            ->all()
                    )
                    ->toHtml();
            },
            [static::class],
        );

        FilamentView::registerRenderHook(
            'inspirecms_content_form_extra_actions',
            function () use ($extraActions) {

                $filteredActions = collect($extraActions)
                    ->filter(fn (Action $action) => ! $action->isHidden())
                    ->all();

                if (empty($filteredActions)) {
                    return;
                }

                return ActionGroup::make([])
                    ->label(__('inspirecms::buttons.more_actions.label'))
                    ->button()
                    ->color('gray')
                    ->dropdownPlacement('top-end')
                    ->actions(
                        collect($filteredActions)
                            ->map(fn (Action $action) => $action->grouped())
                            ->all()
                    )
                    ->toHtml();
            },
            [static::class],
        );
    }

    protected function getFormActions(): array
    {
        if ($this instanceof CreateRecord) {

            return [
                RenderHook::make('inspirecms_content_form_main_actions'),
                $this->getCreateFormAction(),
                $this->getCancelFormAction(),
            ];

        } elseif ($this instanceof EditRecord) {

            $record = $this->getRecord();

            // Guard 2 for trashed record, If the record is trashed, don't show the form actions
            if ($record->trashed()) {
                return [];
            }

            // Guard 3 for locked record, If the record is locked by another user, don't show the form actions
            if ($record->isLocked()) {
                return [];
            }

            return [
                RenderHook::make('inspirecms_content_form_main_actions'),

                $this->getSaveFormAction(),

                RenderHook::make('inspirecms_content_form_extra_actions'),

                $this->getCancelFormAction(),
            ];
        } else {
            return parent::getFormActions() ?? [];
        }
    }

    public function updatedActiveLocale(string $newActiveLocale): void
    {
        if (blank($this->oldActiveLocale)) {
            return;
        }

        $this->resetValidation();

        $translatableAttributes = $this->getTranslatableAttributesForContent();

        // Handle nested title structure
        $dataToStore = [];
        foreach ($translatableAttributes as $attribute) {
            if ($attribute === 'title' && isset($this->data['title']) && is_array($this->data['title'])) {
                // Extract only the old locale's value from the nested title array
                $dataToStore['title'] = $this->data['title'][$this->oldActiveLocale] ?? '';
            } else {
                $dataToStore[$attribute] = $this->data[$attribute] ?? null;
            }
        }

        $this->otherLocaleData[$this->oldActiveLocale] = $dataToStore;

        // Prepare data for new locale
        $newLocaleData = $this->otherLocaleData[$this->activeLocale] ?? [];

        // Handle nested title structure for new locale
        if (isset($this->data['title']) && is_array($this->data['title'])) {
            // If we have a stored value for the new locale, put it back in the array
            if (isset($newLocaleData['title'])) {
                $this->data['title'][$this->activeLocale] = $newLocaleData['title'];
                unset($newLocaleData['title']);
            }
        }

        $this->data = $this->mutuateDataWhileUpdatedActiveLocale(
            Arr::except($this->data, $translatableAttributes),
            $newLocaleData
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

        // Initialize title with all translations for the nested structure
        $allTitleTranslations = [];
        if (in_array('title', $translatableAttributes)) {
            foreach ($this->getTranslatableLocales() as $locale) {
                $allTitleTranslations[$locale] = $record->getTranslation('title', $locale, useFallbackLocale: false);
            }
        }

        foreach ($this->getTranslatableLocales() as $locale) {
            $translatedData = [];

            foreach ($translatableAttributes as $attribute) {
                if ($attribute === 'title') {
                    // For title, use the complete translations array
                    $translatedData[$attribute] = $allTitleTranslations;
                } else {
                    $translatedData[$attribute] = $record->getTranslation($attribute, $locale, useFallbackLocale: false);
                }
            }

            if ($locale !== $this->activeLocale) {
                // For otherLocaleData, store individual locale values (not the full array)
                $storedData = $translatedData;
                if (isset($storedData['title']) && is_array($storedData['title'])) {
                    $storedData['title'] = $storedData['title'][$locale] ?? '';
                }
                $this->otherLocaleData[$locale] = $this->mutateFormDataBeforeFill($storedData);

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
    protected function getTranslatableAttributesForContent(bool $exceptPropertyData = true): array
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

            if ($this instanceof Page) {
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

            $this->propertyDataIsDirtyPreCheck(
                array_merge($record->getLatestVersionPropertyData(), $record->latestContentVersion?->getVersioningCheckDiffData() ?? []),
                array_merge($data['propertyData'] ?? [], $record->contentVersions()->make([...$publishableData, 'publish_state' => $publishableAction])?->getVersioningCheckDiffData() ?? []),
            );

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
        return ! $this instanceof EditRecord;
    }

    protected function propertyDataIsDirtyPreCheck($from, $to)
    {
        try {

            if (! is_array($from) || ! is_array($to)) {
                return;
            }

            if (empty($from) && empty($to)) {
                return;
            }

            $diff = DiffHelper::compareArrays($from, $to);

            // Display notification if no differences found
            if (empty($diff)) {
                Notification::make()
                    ->title(__('inspirecms::resources/content.notification.property_data_not_changed.title'))
                    ->body(__('inspirecms::resources/content.notification.property_data_not_changed.body'))
                    ->info()
                    ->send();

                return;
            }

        } catch (Throwable $th) {
            // throw $th;
        }
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

    // region Actions

    protected function publishAction(): Action
    {
        return Action::make('publish')
            ->label(__('inspirecms::buttons.publish.label'))
            ->modalHeading(__('inspirecms::buttons.publish.heading'))
            ->modalSubmitActionLabel(__('inspirecms::buttons.publish.label'))
            ->successNotificationTitle(__('inspirecms::buttons.publish.messages.success.title'))
            ->keyBindings(['mod+p'])
            ->color('primary')
            ->button()
            ->schema(fn (Schema $schema) => PublishContentForm::configure($schema))
            ->fillForm(fn () => ['published_at' => now()])
            ->beforeFormFilled(function (Action $action) {
                try {
                    $this->validatePublishableData();
                } catch (ValidationException $e) {
                    Notification::make()
                        ->title(__('inspirecms::notification.form_check_error.title'))
                        ->danger()
                        ->send();

                    $action->halt();
                }
            })
            ->action(function (array $data, Action $action) {
                $this->publish($data, $action);
            })
            ->model(InspireCmsConfig::getContentModelClass())
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

    protected function publishDescendantsAndSelfAction(): Action
    {
        return $this->publishAction()
            ->name('publishDescendantsAndSelf')
            ->label(__('inspirecms::buttons.publish_descendants_and_self.label'))
            ->modalHeading(__('inspirecms::buttons.publish_descendants_and_self.heading'))
            ->successNotificationTitle(__('inspirecms::buttons.publish_descendants_and_self.messages.success.title'))
            ->modalSubmitActionLabel(__('inspirecms::buttons.publish.label'))
            ->keyBindings(null)
            ->color('gray')
            ->action(fn (array $data, $action) => $this->publish($data, $action, true));
    }

    // endregion Actions
}
