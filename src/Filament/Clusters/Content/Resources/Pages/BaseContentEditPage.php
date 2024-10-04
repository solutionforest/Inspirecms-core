<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Eloquent\Model;
use Livewire\WithPagination;
use Pboivin\FilamentPeek\Pages\Concerns\HasPreviewModal;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseEditPage;
use SolutionForest\InspireCms\Dtos\ContentDto;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\CanBePublish;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentPageTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\ContentForm;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\HasPublishForm;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Support\TreeNodes\Contracts\HasModelExplorer;

use function Filament\Support\is_app_url;

abstract class BaseContentEditPage extends BaseEditPage implements ContentForm, HasModelExplorer, HasPublishForm
{
    use CanBePublish;
    use ContentPageTrait;
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
}
