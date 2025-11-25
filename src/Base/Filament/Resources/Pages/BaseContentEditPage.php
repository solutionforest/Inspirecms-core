<?php

namespace SolutionForest\InspireCms\Base\Filament\Resources\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;
use SolutionForest\InspireCms\Base\Filament\Concerns\ContentFormTrait;
use SolutionForest\InspireCms\Base\Filament\Concerns\ContentPageTrait;
use SolutionForest\InspireCms\Base\Filament\Concerns\ContentPreviewEditorTrait;
use SolutionForest\InspireCms\Base\Filament\Contracts\ContentForm;
use SolutionForest\InspireCms\Filament\Actions\BackToParentContentAction;
use SolutionForest\InspireCms\Filament\Actions\ContentHistoryAction;
use SolutionForest\InspireCms\Filament\Actions\LockContentAction;
use SolutionForest\InspireCms\Filament\Actions\UnlockContentAction;
use SolutionForest\InspireCms\Filament\Resources\Contents\Actions\AdjustChildOrderAction;
use SolutionForest\InspireCms\Filament\Resources\Contents\Actions\UpdateContentRouteAction;
use SolutionForest\InspireCms\Helpers\FilamentActionHelper;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Models\Contracts\FieldGroup;

use function Filament\Support\is_app_url;

abstract class BaseContentEditPage extends BaseEditRecord implements ContentForm
{
    use ContentFormTrait;
    use ContentPageTrait;
    use ContentPreviewEditorTrait;
    use Translatable {
        ContentFormTrait::updatedActiveLocale insteadof Translatable;
        ContentFormTrait::fillForm insteadof Translatable;
    }

    public function booted(): void
    {
        // Guard 1 for trashed record, If the record is trashed, redirect to the view/index page
        if ($this->getRecord()->trashed()) {
            $redirectUrl = FilamentResourceHelper::attemptToGetUrl(static::getResource(), ['view'], ['record' => $this->getRecord()], true)
                ?? static::getResource()::getUrl('index');
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
        }
    }

    public function mountTranslatable(): void
    {
        // Call trait implementation (if trait provides it)
        if (method_exists($this, 'traitMountTranslatable')) {
            $this->traitMountTranslatable();
        }
        // Ensure query string param takes precedence if present
        if ($locale = request()->query('activeLocale')) {
            $this->activeLocale = $locale;
        }
    }

    protected function getHeaderActions(): array
    {
        return [

            BackToParentContentAction::make(),

            ActionGroup::make([

                ActionGroup::make([

                    ViewAction::make(),

                    DeleteAction::make()
                        ->visible(fn (Model $record) => ! $record->isLocked()),

                    RestoreAction::make(),

                    ForceDeleteAction::make(),

                    LockContentAction::make()
                        ->successRedirectUrl(fn ($record) => $this->getUrl(array_merge(['record' => $record], $this->getRedirectUrlParameters()))),

                    UnlockContentAction::make()
                        ->successRedirectUrl(fn ($record) => $this->getUrl(array_merge(['record' => $record], $this->getRedirectUrlParameters()))),
                ])
                    ->dropdown(false)
                    ->hidden(fn (ActionGroup $action) => FilamentActionHelper::isAnyVisibleActionInActionGroup($action)),

                ActionGroup::make([
                    UpdateContentRouteAction::make(),
                    ContentHistoryAction::make(),
                    AdjustChildOrderAction::make()
                        ->nodeParentId(fn (Content | Model $record) => $record->nestable_tree_id ?? ($record->nestableTree?->getKey() ?? 0))
                        ->hidden(
                            fn (?Model $record) => ! $record instanceof Content ||
                                $record->trashed()
                        )
                        ->successRedirectUrl(function ($record) {
                            return $this->getUrl(['record' => $record, ...$this->getRedirectUrlParameters()]);
                        }),
                ])
                    ->dropdown(false)
                    ->hidden(fn (ActionGroup $action) => FilamentActionHelper::isAnyVisibleActionInActionGroup($action)),
            ]),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label(__('inspirecms::buttons.save_draft.label'))
            ->color('secondary');
    }

    /** * {@inheritDoc} */
    public function getDocumentType()
    {
        return $this->getRecord()->documentType;
    }

    public function getParent(): string | int | Model | null
    {
        return $this->getRecord()->parent;
    }

    public function getParentKey(): string | int | null
    {
        return $this->getRecord()->parent_id;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->getUrl(['record' => $this->getRecord(), ...$this->getRedirectUrlParameters()]);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $translatableAttributes = $this->getTranslatableAttributesForContent();

        $record->fill(Arr::except($data, $translatableAttributes));

        $currentFieldsForType = $record instanceof Content
            ? $record->documentType?->fieldGroups->whereInstanceOf(FieldGroup::class)->mapWithKeys(fn (FieldGroup $fg) => [$fg->name => $fg->fields->pluck('name')->all()])->all()
            : [];
        // Limit the propertyData to the current fields for the type
        $propertyData = Arr::only($data['propertyData'] ?? [], array_keys($currentFieldsForType));
        foreach ($propertyData as $gpKey => $value) {
            if (! is_array($value)) {
                continue;
            }
            $targetFields = $currentFieldsForType[$gpKey] ?? null;
            if (is_null($targetFields) || ! is_array($targetFields) || empty($targetFields)) {
                continue;
            }
            $propertyData[$gpKey] = Arr::only($value, $targetFields);
        }

        // handle 'Property Data' translation here
        $record->setTranslation('propertyData', '', $propertyData);

        foreach (Arr::only($data, $translatableAttributes) as $key => $value) {
            $record->setTranslation($key, $this->activeLocale, $value);
        }

        $originalData = $this->data;

        $existingLocales = null;

        foreach ($this->otherLocaleData as $locale => $localeData) {
            $existingLocales ??= collect($translatableAttributes)
                ->map(fn (string $attribute): array => array_keys($record->getTranslations($attribute)))
                ->flatten()
                ->unique()
                ->all();

            $this->data = [
                ...$this->data,
                ...$localeData,
            ];

            // Since the "propertyData" field is not translatable and already validated before, we skip this.
            unset($this->data['propertyData']);

            try {
                // Validataion for the current locale
                $this->form->validate();
            } catch (ValidationException $exception) {
                if (! array_key_exists($locale, $existingLocales)) {
                    continue;
                }

                $this->setActiveLocale($locale);

                throw $exception;
            }

            $localeData = $this->mutateFormDataBeforeSave($localeData);

            foreach (Arr::only($localeData, $translatableAttributes) as $key => $value) {
                $record->setTranslation($key, $locale, $value);
            }
        }

        $this->data = $originalData;

        $record->save();

        return $record;
    }
}
