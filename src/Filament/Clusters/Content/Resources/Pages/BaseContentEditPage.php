<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use function Filament\Support\is_app_url;
use Livewire\WithPagination;
use Pboivin\FilamentPeek\Pages\Concerns\HasPreviewModal;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseEditPage;
use SolutionForest\InspireCms\Dtos\ContentDto;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\CanBePublish;

use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\ContentForm;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\HasPublishForm;

abstract class BaseContentEditPage extends BaseEditPage implements HasPublishForm, ContentForm
{
    use CanBePublish;
    use HasPreviewModal;
    use WithPagination;

    protected function getHeaderActions(): array
    {
        return [
            \Pboivin\FilamentPeek\Pages\Actions\PreviewAction::make()->icon('heroicon-o-eye'),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
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

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->authorizeAccess();

        $this->wrapPublisableSavingEventIntoDbTransaction(function () {

            $this->callHook('beforeValidate');

            // Avoid save relationships before update
            $data = $this->form->getState(shouldCallHooksBefore: false, afterValidate: null);
            // $data = $this->form->getState(afterValidate: function () {
            //     $this->callHook('afterValidate');

            //     $this->callHook('beforeSave');
            // });

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeSave($data);

            $this->callHook('beforeSave');

            $this->handleRecordUpdate($this->getRecord(), $data);

            // Handle save relationships on this line
            $this->form->model($this->getRecord())->saveRelationships();

            $this->callHook('afterSave');
        });

        $this->rememberData();

        if ($shouldSendSavedNotification) {
            $this->getSavedNotification()?->send();
        }

        if ($shouldRedirect && ($redirectUrl = $this->getRedirectUrl())) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
        }
    }

    //region Computed Property
    public function getDtoProperty()
    {
        return ContentDto::fromModel($this->getRecord());
    }
    //endregion Computed Property
}
