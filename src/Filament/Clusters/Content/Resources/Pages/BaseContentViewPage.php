<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Pboivin\FilamentPeek\Pages\Actions\PreviewAction;
use Pboivin\FilamentPeek\Pages\Concerns\HasPreviewModal;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseViewPage;
use SolutionForest\InspireCms\Dtos\ContentDto;
use SolutionForest\InspireCms\Filament\Actions\ContentHistoryAction;
use SolutionForest\InspireCms\Filament\Actions\ReorderContentAction;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentFormTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentPageTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\ContentForm;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Acitons\BackActon;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Acitons\LockAction;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Acitons\UnlockAction;
use SolutionForest\InspireCms\Helpers\FilamentActionHelper;
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

            BackActon::make(),

            PreviewAction::make()
                ->label(__('inspirecms::resources/content.actions.preview.label'))
                ->hidden(fn (Model $record) => $record->trashed()),

            Actions\EditAction::make()->iconButton(),

            Actions\ActionGroup::make([

                Actions\ActionGroup::make([

                    Actions\DeleteAction::make()
                        ->visible(fn (Model $record) => ! $record->isLocked()),

                    Actions\RestoreAction::make(),

                    Actions\ForceDeleteAction::make(),

                    LockAction::make()
                        // refresh title
                        ->successRedirectUrl(fn ($record) => $this->getUrl(array_merge(['record' => $record], $this->getRedirectUrlParameters()))),

                    UnlockAction::make()
                            // refresh title
                        ->successRedirectUrl(fn ($record) => $this->getUrl(array_merge(['record' => $record], $this->getRedirectUrlParameters()))),
                ])
                    ->dropdown(false)
                    ->hidden(fn (Actions\ActionGroup $action) => FilamentActionHelper::isAnyVisibleActionInActionGroup($action)),

                Actions\ActionGroup::make([
                    ContentHistoryAction::make(),
                    ReorderContentAction::make(),
                ])
                    ->dropdown(false)
                    ->hidden(fn (Actions\ActionGroup $action) => FilamentActionHelper::isAnyVisibleActionInActionGroup($action)),
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
        
        $templateContent = $template?->getContent();

        if (! $template || blank($templateContent)) {
            Notification::make()
                ->title(__('inspirecms::notification.template_not_found.title'))
                ->body(__('inspirecms::notification.template_not_found.body'))
                ->danger()
                ->seconds(60)
                ->send();

            throw new Halt;
        }

        return $templateContent;
    }

    public static function renderPreviewModalView(string $view, array $data): string
    {
        return Blade::render($view, $data);
    }

    protected function mutatePreviewModalData(array $data): array
    {
        $contentDto = $this->contentDto;

        $locale = $this->getActiveFormsLocale();
        if ($contentDto instanceof ContentDto) {
            // Set the locale of the content dto to the active locale
            $contentDto->setLocale($locale);
        }

        $data['content'] = $contentDto;
        $data['locale'] = $locale;

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
