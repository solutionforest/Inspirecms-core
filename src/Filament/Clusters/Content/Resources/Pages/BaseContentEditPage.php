<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Livewire\WithPagination;
use Pboivin\FilamentPeek\Pages\Concerns\HasPreviewModal;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseEditPage;
use SolutionForest\InspireCms\Dtos\ContentDto;
use SolutionForest\InspireCms\Filament\Actions\ContentHistoryAction;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentFormTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentPageTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\ContentForm;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Support\TreeNodes\Contracts\HasModelExplorer;

use function Filament\Support\is_app_url;

abstract class BaseContentEditPage extends BaseEditPage implements ContentForm, HasModelExplorer
{
    use ContentFormTrait;
    use ContentPageTrait;
    use EditRecord\Concerns\Translatable{
        updatedActiveLocale as protected traitUpdatedActiveLocale;
    }
    use HasPreviewModal;
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
            Actions\LocaleSwitcher::make(),
            ContentHistoryAction::make()
                ->record(fn () => $this->getRecord()),
            \Pboivin\FilamentPeek\Pages\Actions\PreviewAction::make()
                ->iconButton(),
            Actions\DeleteAction::make()
                ->iconButton(),
            Actions\RestoreAction::make()
                ->iconButton(),
            Actions\ForceDeleteAction::make()
                ->iconButton(),
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

    protected function getPreviewModalView(): ?string
    {
        /** @var ContentDto */
        $dto = $this->dto;
        $template = $dto->getDefaultTemplate();
        $templateName = $template?->viewName;
        if (blank($templateName)) {
            Notification::make()
                ->title(__('inspirecms::notification.template_file_not_found.title'))
                ->body(__('inspirecms::notification.template_file_not_found.body'))
                ->danger()
                ->send();

            throw new Halt;
        }

        return $templateName;
    }

    protected function mutatePreviewModalData(array $data): array
    {
        unset($data['record']);
        $data['content'] = $this->dto;

        return $data;
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label(__('inspirecms::actions.save_draft.label'))
            ->color('secondary');
    }

    //region Computed Property
    public function getDtoProperty()
    {
        return ContentDto::fromModel($this->getRecord());
    }
    //endregion Computed Property

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
        $translatableAttributes = static::getResource()::getTranslatableAttributes();

        // Filter out the propertyDataTranslation
        $translatableAttributes = Arr::where($translatableAttributes, fn ($attribute) => $attribute != 'propertyData');

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
}
