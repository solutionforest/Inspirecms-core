<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Livewire\WithPagination;
use Pboivin\FilamentPeek\Pages\Concerns\HasPreviewModal;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseViewPage;
use SolutionForest\InspireCms\Dtos\ContentDto;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\CanBePublish;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentPageTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\ContentForm;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\HasPublishForm;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Support\TreeNodes\Contracts\HasModelExplorer;

abstract class BaseContentViewPage extends BaseViewPage implements ContentForm, HasModelExplorer, HasPublishForm
{
    use CanBePublish;
    use ContentPageTrait;
    use HasPreviewModal;
    use WithPagination;

    protected static string $view = 'inspirecms::filament.pages.content.view';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label(__('inspirecms::inspirecms.back'))
                ->url(fn () => FilamentResourceHelper::attemptToGetUrl(static::getResource(), ['trash', 'index'], [], false))
                ->color('gray')
                ->visible(fn ($record) => $record->trashed()),
            Actions\EditAction::make()
                ->hidden(fn ($record) => $record->trashed())
                ->iconButton(),
            \Pboivin\FilamentPeek\Pages\Actions\PreviewAction::make()
                ->hidden(fn ($record) => $record->trashed())
                ->iconButton(),
            Actions\DeleteAction::make()
                ->iconButton(),
            Actions\RestoreAction::make()
                ->iconButton()
                ->successRedirectUrl(fn () => FilamentResourceHelper::attemptToGetUrl(static::getResource(), ['index'], [], false)),
            Actions\ForceDeleteAction::make()
                ->iconButton(),
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

    //region Computed Property
    public function getDtoProperty()
    {
        return ContentDto::fromModel($this->getRecord());
    }
    //endregion Computed Property
}
