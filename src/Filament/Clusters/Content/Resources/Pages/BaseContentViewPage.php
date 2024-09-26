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
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\HasPublishForm;

abstract class BaseContentViewPage extends BaseViewPage implements HasPublishForm
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
