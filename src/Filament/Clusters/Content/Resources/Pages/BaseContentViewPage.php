<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Exceptions\Halt;
use Livewire\WithPagination;
use Pboivin\FilamentPeek\Pages\Actions\PreviewAction;
use Pboivin\FilamentPeek\Pages\Concerns\HasPreviewModal;
use Pboivin\FilamentPeek\Support\Html;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseViewPage;
use SolutionForest\InspireCms\Dtos\ContentDto;
use SolutionForest\InspireCms\Filament\Actions\ContentHistoryAction;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentFormTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentPageTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\ContentForm;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Support\TreeNodes\Contracts\HasModelExplorer;

abstract class BaseContentViewPage extends BaseViewPage implements ContentForm, HasModelExplorer
{
    use ContentFormTrait;
    use ContentPageTrait;
    use HasPreviewModal;
    use ViewRecord\Concerns\Translatable {
        updatedActiveLocale as protected traitUpdatedActiveLocale;
    }
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
            Actions\LocaleSwitcher::make(),
            PreviewAction::make(),
            ContentHistoryAction::make()
                ->record(fn () => $this->getRecord()),
            Actions\EditAction::make()
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

    public function updatedActiveLocale(string $newActiveLocale): void
    {
        $this->updatedActiveLocaleForContent($newActiveLocale);
    }

    //region Preview
    protected function getPreviewModalView(): ?string
    {
        $record = $this->getRecord();
        $template = $record->getDefaultTemplate();
        $template ??= $this->getDocumentType()?->getDefaultTemplate();
        if (! $template) {
            Notification::make()
                ->title(__('inspirecms::notification.template_file_not_found.title'))
                ->body(__('inspirecms::notification.template_file_not_found.body'))
                ->danger()
                ->send();

            throw new Halt;
        }

        return $template->getViewFullName();
    }

    protected function mutatePreviewModalData(array $data): array
    {
        $data['content'] = $this->contentDto;

        return $data;
    }
    public static function renderPreviewModalView(string $view, array $data): string
    {
        return Html::injectPreviewModalStyle(
            view('inspirecms::filament-peek.preview', [
                'templateData' => $data,
                'templateView' => $view,
            ])->render()
        );
    }
    //endregion Preview

    //region Computed properties
    #[\Livewire\Attributes\Computed(persist: true, seconds: 7200)]
    public function contentDto()
    {
        /**
         * @var ContentDto
         */
        $dto = ContentDto::fromTranslatableModel($this->getRecord(), $this->getActiveFormsLocale());
        $dto->setPropertyData($this->getRecord()->getLatestVersionPropertyData());
        return $dto;
    }
    //endregion Computed properties
}
