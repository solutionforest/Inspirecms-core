<?php

namespace SolutionForest\InspireCms\Livewire;

use Filament\Actions\Action;
use Filament\Support\Enums\IconPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Filament\Resources\ContentResource;
use SolutionForest\InspireCms\Filament\TreeNode\Actions\CreateContentItemAction;
use SolutionForest\InspireCms\Filament\TreeNode\Actions\DeleteContentItemAction;
use SolutionForest\InspireCms\Filament\TreeNode\Actions\MoveContentAction;
use SolutionForest\InspireCms\Filament\TreeNode\Actions\ReorderContentItemAction;
use SolutionForest\InspireCms\Filament\TreeNode\Actions\SetDefaultContentPageAction;
use SolutionForest\InspireCms\Filament\TreeNode\Actions\UpdateContentItemRouteAction;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Support\TreeNode\Actions\Action as TreeNodeAction;
use SolutionForest\InspireCms\Support\TreeNode\Actions\ActionGroup;
use SolutionForest\InspireCms\Support\TreeNode\ModelExplorer;

class ContentSidebar extends BaseContentTreeNode
{
    public array $redirectUrlParameters = [];

    public ?string $activeLocale = null;

    public ?string $pageName = null;

    public bool $isExpandedSidebar = true;

    public bool $isSpaMode = true;

    public function mount()
    {
        $this->cacheModelExplorerNodesOn(parentKey: $this->getModelRootLevelParentId());

        // Init selected model item
        if (! empty($this->selectedModelItemKeys)) {
            $record = $this->resolveSelectedModelItems($this->selectedModelItemKeys)->first();
            // Do not show seleccted record if it is a table node
            if ($this->isDisplayChildrenAsTable($record?->parent)) {
                $this->setSelectedModelItem([$record->parent->getKey()], merge: false, replace: true);
            } 
            // Expand ancestors nodes
            else {
                $this->expandParentModelItemIfSelected($this->selectedModelItemKeys);
            }
        }

        if (! isset($this->activeLocale)) { // set default locale if not set
            $this->activeLocale = Arr::first($this->getTranslatableLocales());
        }
    }

    // region Locale config
    public function localeSwitcher(): \Filament\Actions\Action
    {
        return \Filament\Actions\LocaleSwitcher::make();
    }

    public function getTranslatableLocales(): array
    {
        return array_keys(InspireCms::getAllAvailableLanguages());
    }

    public function updatedActiveLocale(string $locale): void
    {
        $this->activeLocale = $locale;
        // dispatch event to page component
        $this->dispatch('changeActiveLocale', $locale);
    }
    // endregion Locale config

    public function modelExplorer(ModelExplorer $modelExplorer): ModelExplorer
    {
        return parent::modelExplorer($modelExplorer)
            ->maxSelectItem(1)
            ->resolveRecordUsing(function ($query, $key) {
                return $query
                    ->with('locked')
                    ->with('parent.documentType') // for checking is table node
                    ->find($key);
            })
            ->determineItemHasChildrenUsing(function (Model | Content $record) {

                if ($this->isDisplayChildrenAsTable($record)) {
                    return false;
                }

                return $record->children_count > 0;
            })
            ->determineItemIconUsing(fn (Model | Content $record) => $record->documentType?->icon)
            ->determineItemUrlUsing(function (Model | Content $record) {

                $itemUrlParams = array_merge([
                    'record' => $record->getKey(),
                    'activeRelationManager' => 0,
                ], $this->redirectUrlParameters, [
                    'locale' => $this->activeLocale,
                ]);

                $resource = static::getResource();
                // authorize user to view/edit the record
                $page = FilamentResourceHelper::retrieveFirstAccessiblePage($resource, ['edit', 'view'], ['record' => $record]);

                if (! $page) {
                    return null;
                }

                return FilamentResourceHelper::attemptToGetUrl(
                    $resource,
                    $page,
                    $itemUrlParams,
                    false
                );
            })
            ->mutuateNodeItemsUsing(function (array $item, Model | Content $record): array {

                if (in_array('Spatie\Translatable\HasTranslations', class_uses_recursive($record))) {
                    $item['title'] = $record->getTranslations('title');
                    $item['fallbackTitle'] = $record->getTranslation('title', $record->getFallbackLocale());
                    if (blank($item['fallbackTitle'])) {
                        $item['fallbackTitle'] = collect($record->getTranslations('title'))->filter()->first();
                    }
                }

                $item['documentTypeKey'] = $record->document_type_id;
                $item['documentTypeCat'] = $record->documentType?->category;

                $item['tree_id'] = isset($record->nestable_tree_id)
                    ? $record->nestable_tree_id
                    : ($record->nestableTree ? $record->nestableTree->getKey() : 'na');

                return $item;
            })
            ->determineItemIsDisabledUsing(function (Model | Content $record) {
                return $record->isLocked();
            })
            ->actions([
                CreateContentItemAction::make(),
                ReorderContentItemAction::make('reorder_content_item'),

                ActionGroup::make([

                    SetDefaultContentPageAction::make(),

                    UpdateContentItemRouteAction::make(),

                    ActionGroup::make([
                        MoveContentAction::make('move_content_to_under_root')->moveUnderRoot(true),
                        MoveContentAction::make()->moveUnderRoot(false),
                    ])
                        ->button()
                        ->color('gray')
                        ->icon(null)
                        ->iconPosition(IconPosition::After)
                        ->icon('heroicon-o-arrow-right')
                        ->dropdownPlacement('right-start')
                        ->label(__('inspirecms::buttons.move_to.label'))
                        ->extraAttributes(['class' => 'w-full justify-between']),

                    DeleteContentItemAction::make(),

                ])->dropdown(false)->hidden(fn ($itemKey) => $itemKey === 'root'),
            ]);
    }

    protected function getModelExplorerItemsFrom(string | int $parentKey): array
    {
        if (! $this->isValidSelectableModelItemKey($parentKey)) {
            return [];
        }

        $selectItem = $this->resolveSelectedModelItems($parentKey)->first();

        // Hide children
        if ($this->isDisplayChildrenAsTable($selectItem)) {
            return [];
        }

        return parent::getModelExplorerItemsFrom($parentKey);
    }

    protected function mutuateModelExplorerNodes($records, string | int $parentKey): array
    {
        foreach ($records as $record) {
            if ($record instanceof Content && $record instanceof Model) {
                $key = $record->getKey();
                $this->cachedModelExplorerRecord($key, $record);
            }
        }

        return parent::mutuateModelExplorerNodes($records, $parentKey);
    }

    public function getGroupedNodeItems()
    {
        return collect(parent::getGroupedNodeItems())
            ->prepend([
                'key' => 'root',
                'parentKey' => -1,
                'depth' => -1,
                'title' => __('inspirecms::inspirecms.root'),
                'hasChildren' => false,
                'link' => FilamentResourceHelper::attemptToGetUrl(static::getResource(), ['index'], $this->getRedirectUrlParameters(), false),
                'documentTypeKey' => null,
                'extraAttributes' => [
                    'title' => ['class' => ['font-bold']],
                    'ctn' => ['class' => ['h-11 shadow']],
                ],
            ])
            ->all();
    }

    public function render()
    {
        return view('inspirecms::livewire.content-sidebar', [
            'translatableLocale' => $this->activeLocale,
            'translatable' => filled($this->activeLocale),
            'modelExplorer' => $this->getModelExplorer(),
        ]);
    }

    /**
     * @return class-string<\Filament\Resources\Resource>
     */
    protected static function getResource()
    {
        return InspireCmsConfig::getFilamentResource('content', ContentResource::class);
    }

    protected function isValidSelectableModelItemKey($key): bool
    {
        if (is_string($key)) {
            return parent::isValidSelectableModelItemKey($key) &&
                $key != 'root';
        }

        return parent::isValidSelectableModelItemKey($key);
    }

    protected function getRedirectUrlParameters()
    {
        return array_merge($this->redirectUrlParameters, [
            'locale' => $this->activeLocale,
        ]);
    }

    protected function configureSelectedModelItemFormAction(Action | TreeNodeAction $action): void
    {
        // mount record
        if (
            $action instanceof DeleteContentItemAction ||
            $action instanceof SetDefaultContentPageAction ||
            $action instanceof MoveContentAction ||
            $action instanceof UpdateContentItemRouteAction ||
            $action instanceof ReorderContentItemAction
        ) {

            $action->record(function ($action, $itemKey, $treeNode, $livewire) {

                if ($itemKey == 'root') {
                    return null;
                }

                return $this->resolveSelectedModelItems($itemKey)->first();

            });
        }

        switch (true) {
            case $action instanceof CreateContentItemAction:

                $action
                    ->color('primary')
                    ->parentContentKey(function ($itemKey) {
                        if (blank($itemKey) || $itemKey === 'root') {
                            return null;
                        }

                        return $itemKey;
                    })
                    ->parentDocumentType(function ($itemKey, $livewire) {

                        $item = filled($itemKey) ? $livewire->getCacheModelItemNode($itemKey) : [];

                        return $item['documentTypeKey'] ?? null;
                    })
                    ->nodeTitleUsing(function ($itemKey, $livewire, $treeNode) {

                        if ($itemKey === 'root') {
                            return __('inspirecms::inspirecms.root');
                        }

                        $item = $livewire->getCacheModelItemNode($itemKey);

                        if (! is_array($item)) {
                            return null;
                        }

                        $translatableLocale = method_exists($livewire, 'getActiveActionsLocale') ? $livewire->getActiveActionsLocale() : null;

                        return $treeNode->getTitleForItem($item, $translatableLocale);
                    });

                break;

            case $action instanceof DeleteContentItemAction:
            case $action instanceof SetDefaultContentPageAction:
            case $action instanceof MoveContentAction:

                $action
                    ->successRedirectUrl(fn () => FilamentResourceHelper::attemptToGetUrl(static::getResource(), 'index', $this->getRedirectUrlParameters(), false));

                break;

            case $action instanceof ReorderContentItemAction:

                $action
                    ->nodeParentId(function ($itemKey, $treeNode, $record, $livewire) {

                        $item = filled($itemKey) ? $livewire->getCacheModelItemNode($itemKey) : [];

                        if ($itemKey === 'root' || blank($itemKey) || ! isset($item['tree_id'])) {
                            return app(static::getModel())->getNestableTreeRootLevelParentId();
                        } else {
                            return $item['tree_id'];
                        }
                    })
                    ->successRedirectUrl(function () {
                        $pageName = $this->pageName ?? 'index';
                        $recordKey = Arr::last($this->selectedModelItemKeys);
                        if ($recordKey && in_array($pageName, ['edit', 'view'])) {
                            return FilamentResourceHelper::attemptToGetUrl(
                                static::getResource(),
                                $pageName,
                                ['record' => $recordKey, ...$this->getRedirectUrlParameters()],
                                false
                            );
                        } elseif (filled($pageName)) {
                            return FilamentResourceHelper::attemptToGetUrl(static::getResource(), 'index', $this->getRedirectUrlParameters(), false);
                        }

                        return null;
                    });

                break;
            default:
                break;
        }

        $this->cacheAction($action);

    }

    private function isDisplayChildrenAsTable($record): bool
    {
        return $record && $record->documentType->show_as_table;
    }
}
