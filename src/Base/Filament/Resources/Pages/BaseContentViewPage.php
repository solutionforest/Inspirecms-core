<?php

namespace SolutionForest\InspireCms\Base\Filament\Resources\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable;
use Livewire\Attributes\Computed;
use Pboivin\FilamentPeek\Pages\Actions\PreviewAction;
use Pboivin\FilamentPeek\Pages\Concerns\HasPreviewModal;
use SolutionForest\InspireCms\Base\Filament\Concerns\ContentFormTrait;
use SolutionForest\InspireCms\Base\Filament\Concerns\ContentPageTrait;
use SolutionForest\InspireCms\Base\Filament\Contracts\ContentForm;
use SolutionForest\InspireCms\Factories\PreviewFactory;
use SolutionForest\InspireCms\Filament\Actions\BackToParentContentAction;
use SolutionForest\InspireCms\Filament\Actions\ContentHistoryAction;
use SolutionForest\InspireCms\Filament\Actions\LockContentAction;
use SolutionForest\InspireCms\Filament\Actions\ReorderContentAction;
use SolutionForest\InspireCms\Filament\Actions\UnlockContentAction;
use SolutionForest\InspireCms\Helpers\FilamentActionHelper;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Models\Contracts\Content;

abstract class BaseContentViewPage extends BaseViewRecord implements ContentForm
{
    use ContentFormTrait;
    use ContentPageTrait;
    use HasPreviewModal;
    use Translatable {
        ContentFormTrait::updatedActiveLocale insteadof Translatable;
        ContentFormTrait::fillForm insteadof Translatable;
    }

    protected function getHeaderActions(): array
    {
        return [

            BackToParentContentAction::make(),

            PreviewAction::make()
                ->label(__('inspirecms::buttons.preview.label'))
                ->hidden(fn (Model $record) => $record->trashed()),

            EditAction::make()->iconButton(),

            ActionGroup::make([

                ActionGroup::make([

                    DeleteAction::make()
                        ->visible(fn (Model $record) => ! $record->isLocked()),

                    RestoreAction::make(),

                    ForceDeleteAction::make(),

                    LockContentAction::make()
                        ->successRedirectUrl(fn ($record) => $this->getUrl(array_merge(['record' => $record], $this->getRedirectUrlParameters()))),

                    UnlockContentAction::make()
                        ->successRedirectUrl(fn ($record) => $this->getUrl(array_merge(['record' => $record], $this->getRedirectUrlParameters()))),
                ])
                    ->dropdown(false)
                    ->hidden(fn (ActionGroup $action) => FilamentActionHelper::isAnyVisibleActionInActionGroup($action)),

                ActionGroup::make([
                    ContentHistoryAction::make(),
                    ReorderContentAction::make(),
                ])
                    ->dropdown(false)
                    ->hidden(fn (ActionGroup $action) => FilamentActionHelper::isAnyVisibleActionInActionGroup($action)),
            ]),
        ];
    }

    protected function configureAction(Action $action): void
    {
        parent::configureAction($action);

        $resource = static::getResource();

        switch (true) {
            case $action instanceof RestoreAction:
                $action
                    ->successRedirectUrl(fn () => FilamentResourceHelper::attemptToGetUrl($resource, ['index'], [], false));

                break;
            case $action instanceof EditAction:

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
        return 'handle by previewFactory';
    }

    public static function renderPreviewModalView(string $view, array $data): string
    {
        $extraData = Arr::except($data, [
            'propertyData',
            'content',
            'documentType',
            'template',
            'record',
        ]);

        return PreviewFactory::create()->renderContentPreview(
            documentType: $data['documentType'] ?? null,
            template: $data['template'] ?? null,
            content: $data['content'] ?? null,
            propertyData: $data['propertyData'] ?? [],
            locale: $data['locale'] ?? null,
            data: $extraData,
        );
    }

    protected function mutatePreviewModalData(array $data): array
    {
        $locale = $this->getActiveFormsLocale();
        $content = $this->getRecord();

        $data['propertyData'] = $content->getLatestPublishedPropertyData();
        $data['content'] = $content;
        $data['documentType'] = $content->documentType;
        $data['template'] = $content->getDefaultTemplate() ?? $content->documentType?->getDefaultTemplate();
        $data['contentDTO'] = $this->contentDto;
        $data['locale'] = $locale;

        return $data;
    }
    // endregion Preview

    // region Computed properties
    #[Computed(persist: true, seconds: 7200)]
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
