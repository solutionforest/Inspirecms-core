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
use SolutionForest\InspireCms\Filament\Actions\ReorderContentAction;
use SolutionForest\InspireCms\Filament\Actions\UpdateRouteAction;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentFormTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentPageTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentPreviewEditorTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\ContentForm;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;

use function Filament\Support\is_app_url;

abstract class BaseContentEditPage extends BaseEditPage implements ContentForm
{
    use ContentFormTrait;
    use ContentPageTrait;
    use ContentPreviewEditorTrait;
    use EditRecord\Concerns\Translatable{
        ContentFormTrait::updatedActiveLocale insteadof EditRecord\Concerns\Translatable;
        ContentFormTrait::fillForm insteadof EditRecord\Concerns\Translatable;
    }
    use WithPagination;

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
            Actions\ActionGroup::make([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\DeleteAction::make(),
                    Actions\RestoreAction::make(),
                    Actions\ForceDeleteAction::make(),
                ])->dropdown(false),
                Actions\ActionGroup::make([
                    UpdateRouteAction::make(),
                    ContentHistoryAction::make(),
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
            \Filament\Actions\ActionGroup::make([
                $this->getPublishFormAction('edit', $this->getRecord()),
                $this->getPublishFormAction('edit', $this->getRecord())
                    ->name('publish_descendants_and_self')
                    ->label(__('inspirecms::resources/content.actions.publish_descendants_and_self.label'))
                    ->modalHeading(__('inspirecms::resources/content.actions.publish_descendants_and_self.modal.heading'))
                    ->successNotificationTitle(__('inspirecms::resources/content.actions.publish_descendants_and_self.notification.success.title'))
                    ->modalSubmitActionLabel(__('inspirecms::resources/content.actions.publish_descendants_and_self.modal.actions.publish.label'))
                    ->keyBindings(null)
                    ->color('gray')
                    ->action(fn (array $data, $action) => $this->publish($data, $action, true)),
            ])->label(__('inspirecms::resources/content.actions.publish.label'))->button(),
            $this->getSaveFormAction(),
            \Filament\Actions\ActionGroup::make(inspirecms_content_statuses()->getFormActions())
                ->label(__('inspirecms::resources/content.actions.more_actions.label'))
                ->button()
                ->color('gray'),
            $this->getCancelFormAction(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label(__('inspirecms::resources/content.actions.save_draft.label'))
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
        $translatableAttributes = $this->getTranslatableAttributes();

        $record->fill(Arr::except($data, $translatableAttributes));

        // handle 'Property Data' translation here
        $record->setTranslation('propertyData', '', $data['propertyData'] ?? []);

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

    protected function configureAction(Actions\Action $action): void
    {
        parent::configureAction($action);

        switch (true) {
            case $action instanceof ReorderContentAction:
                $action
                    ->nodeParentId(fn (\SolutionForest\InspireCms\Models\Contracts\Content | Model $record) => $record->nestable_tree_id ?? ($record->nestableTree?->getKey() ?? 0))
                    ->hidden(
                        fn (?Model $record) => ! $record instanceof \SolutionForest\InspireCms\Models\Contracts\Content ||
                        $record->trashed()
                    )->successRedirectUrl(function ($record) {
                        return $this->getUrl(['record' => $record, ...$this->getRedirectUrlParameters()]);
                    });

                break;
        }
    }
}
