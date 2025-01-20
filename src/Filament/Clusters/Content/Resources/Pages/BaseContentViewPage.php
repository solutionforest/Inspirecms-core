<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Blade;
use Pboivin\FilamentPeek\Pages\Actions\PreviewAction;
use Pboivin\FilamentPeek\Pages\Concerns\HasPreviewModal;
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

abstract class BaseContentViewPage extends BaseViewPage implements ContentForm
{
    use ContentFormTrait;
    use ContentPageTrait;
    use HasPreviewModal;
    use ViewRecord\Concerns\Translatable {
        ContentFormTrait::updatedActiveLocale insteadof ViewRecord\Concerns\Translatable;
        ContentFormTrait::fillForm insteadof ViewRecord\Concerns\Translatable;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label(__('inspirecms::resources/content.actions.back.label'))
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
            PreviewAction::make()
                ->label(__('inspirecms::resources/content.actions.preview.label')),
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

    protected function configureAction(Actions\Action $action): void
    {
        parent::configureAction($action);

        $resource = static::getResource();

        switch (true) {
            case $action instanceof Actions\RestoreAction:
                $action
                    ->successRedirectUrl(fn () => FilamentResourceHelper::attemptToGetUrl($resource, ['index'], [], false));

                break;
            case $action instanceof Actions\EditAction:

                if ($resource::hasPage('edit')) {
                    $action->url(fn (): string => static::getResource()::getUrl('edit', ['record' => $this->getRecord(), ...$this->getRedirectUrlParameters()]));
                }

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
                        return $this->getUrl(['record' => $record, ...$this->getRedirectUrlParameters()]);
                    });

                break;
        }
    }

    // region Preview
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

        return $template->getContent();
    }

    public static function renderPreviewModalView(string $view, array $data): string
    {
        return Blade::render($view, $data);
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
    // endregion Preview

    // region Computed properties
    #[\Livewire\Attributes\Computed(persist: true, seconds: 7200)]
    public function contentDto()
    {
        $content = $this->getRecord();

        if ($content instanceof Content) {
            return $content->toPreviewDto(
                $content,
                $content->getLatestPublishedPropertyData(),
                $this->getActiveFormsLocale(),
                $content->documentType,
            );
        }

        return null;
    }
    // endregion Computed properties
}
