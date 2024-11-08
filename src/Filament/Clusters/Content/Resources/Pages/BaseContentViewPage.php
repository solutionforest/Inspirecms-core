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
use SolutionForest\InspireCms\Filament\Actions\BackToParentContentAction;
use SolutionForest\InspireCms\Filament\Actions\ContentHistoryAction;
use SolutionForest\InspireCms\Filament\Actions\ReorderContentAction;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentFormTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentPageTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\ContentForm;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Support\TreeNodes\Contracts\HasModelExplorer;

abstract class BaseContentViewPage extends BaseViewPage implements ContentForm, HasModelExplorer
{
    use ContentFormTrait;
    use ContentPageTrait;
    use HasPreviewModal;
    use ViewRecord\Concerns\Translatable {
        updatedActiveLocale as protected traitUpdatedActiveLocale;
    }
    use WithPagination;

    protected static string $view = 'inspirecms::filament.pages.content.view';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label(__('inspirecms::inspirecms.back'))
                ->color('gray')
                ->url(function ($record) {
                    if ($record->trashed()) {
                        return FilamentResourceHelper::attemptToGetUrl(static::getResource(), ['trash', 'index'], [], false);
                    }

                    return null;
                })
                ->visible(function (Actions\Action $action) {
                    return filled($action->getUrl());
                }),
            BackToParentContentAction::make(),
            Actions\LocaleSwitcher::make(),
            PreviewAction::make()
                ->label(__('inspirecms::actions.preview.label')),
            Actions\EditAction::make()->iconButton(),
            Actions\ActionGroup::make([
                Actions\ActionGroup::make([
                    Actions\DeleteAction::make(),
                    Actions\RestoreAction::make(),
                    Actions\ForceDeleteAction::make(),
                ])->dropdown(false),
                Actions\ActionGroup::make([
                    ContentHistoryAction::make(),
                    ReorderContentAction::make(),
                ])->dropdown(false),
            ]),
        ];
    }

    public function updatedActiveLocale(string $newActiveLocale): void
    {
        $this->updatedActiveLocaleForContent($newActiveLocale);
    }

    protected function configureAction(Actions\Action $action): void
    {
        parent::configureAction($action);

        switch (true) {
            case $action instanceof Actions\RestoreAction:
                $action
                    ->successRedirectUrl(fn () => FilamentResourceHelper::attemptToGetUrl(static::getResource(), ['index'], [], false));

                break;
            case $action instanceof Actions\EditAction:
                $action
                    ->hidden(fn ($record) => $record->trashed());

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
        $contentDto = $this->contentDto;

        if ($contentDto instanceof ContentDto) {
            // Set the locale of the content dto to the active locale
            $contentDto->setLocale($this->getActiveFormsLocale());
        }

        $data['content'] = $contentDto;

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
        $content = $this->getRecord();

        if ($content instanceof Content) {
            return $content->toPreviewDto(
                $content,
                $content->getLatestPublishedPropertyData(),
                $this->getActiveFormsLocale(),
                inspirecms()->getFallbackLanguage()?->code ?? app()->getLocale(),
                $content->documentType,
            );
        }

        return null;
    }
    //endregion Computed properties
}
