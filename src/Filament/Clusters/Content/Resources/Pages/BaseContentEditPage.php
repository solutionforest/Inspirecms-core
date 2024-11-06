<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Livewire\WithPagination;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseEditPage;
use SolutionForest\InspireCms\Filament\Actions\BackToParentContentAction;
use SolutionForest\InspireCms\Filament\Actions\ContentHistoryAction;
use SolutionForest\InspireCms\Filament\Actions\LinkContentToParentAction;
use SolutionForest\InspireCms\Filament\Actions\ReorderContentAction;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentFormTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentPageTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentPreviewEditorTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\ContentForm;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Support\TreeNodes\Contracts\HasModelExplorer;

use function Filament\Support\is_app_url;

abstract class BaseContentEditPage extends BaseEditPage implements ContentForm, HasModelExplorer
{
    use ContentFormTrait;
    use ContentPageTrait;
    use ContentPreviewEditorTrait;
    use EditRecord\Concerns\Translatable{
        updatedActiveLocale as protected traitUpdatedActiveLocale;
    }
    use WithPagination;

    protected static string $view = 'inspirecms::filament.pages.content.edit';

    public function booted(): void
    {
        // Guard 1 for trashed record, If the record is trashed, redirect to the view/index page
        if ($this->getRecord()->trashed()) {
            $redirectUrl = FilamentResourceHelper::attemptToGetUrl(static::getResource(), ['view'], ['record' => $this->getRecord()], true)
                ?? static::getResource()::getUrl('index');
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            BackToParentContentAction::make(),
            Actions\LocaleSwitcher::make(),
            Actions\ActionGroup::make([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\DeleteAction::make(),
                    Actions\RestoreAction::make(),
                    Actions\ForceDeleteAction::make(),
                ])->dropdown(false),
                Actions\ActionGroup::make([
                    ContentHistoryAction::make(),
                    // Some logic change (documentType also is nestable now)
                    // LinkContentToParentAction::make(),
                    ReorderContentAction::make(),
                ])->dropdown(false),
            ]),
        ];
    }

    protected function getFormActions(): array
    {
        // Guard 2 for trashed record, If the record is trashed, don't show the form actions
        if ($this->getRecord()->trashed()) {
            return [];
        }

        return [
            $this->getPublishFormAction('edit', $this->getRecord()),
            $this->getSaveFormAction(),
            \Filament\Actions\ActionGroup::make([])
                ->label(__('inspirecms::actions.more_actions.label'))
                ->button()
                ->color('gray')
                ->actions(array_filter([
                    inspirecms_content_statuses()->getOption('unpublish')->getFormAction(),
                    inspirecms_content_statuses()->getOption('private')->getFormAction(),
                ])),
            $this->getCancelFormAction(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label(__('inspirecms::actions.save_draft.label'))
            ->color('secondary');
    }

    public function getDocumentType(): int | string | Model
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
        return $this->getUrl(['record' => $this->getRecord()]);
    }

    public function updatedActiveLocale(string $newActiveLocale): void
    {
        $this->updatedActiveLocaleForContent($newActiveLocale);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $translatableAttributes = $this->getTranslatableAttributes();

        $record->fill(Arr::except($data, $translatableAttributes));

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

            try {
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

        // handle 'Property Data' translation here
        $record->setTranslation('propertyData', '', $data['propertyData'] ?? []);

        $this->data = $originalData;

        $record->save();

        return $record;
    }

    protected function configureAction(Actions\Action $action): void
    {
        parent::configureAction($action);

        switch (true) {
            case $action instanceof LinkContentToParentAction:
                $action
                    ->hidden(
                        fn ($record) => $record->trashed()
                    );

                break;
            case $action instanceof ReorderContentAction:
                $action
                    ->nodeParentId(fn ($record) => $record->getParentNestableTreeId())
                    ->hidden(
                        fn ($record) => ! method_exists($record, 'getParentId') ||
                        $record->trashed()
                    )->successRedirectUrl(function ($record) {
                        return $this->getUrl(['record' => $record]);
                    });

                break;
        }
    }
}
