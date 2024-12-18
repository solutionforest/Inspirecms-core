<?php

namespace SolutionForest\InspireCms\Livewire;

use Filament\Actions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Filament\TreeNode\Actions\CreateContentItemAction;
use SolutionForest\InspireCms\Filament\TreeNode\Actions\DeleteContentItemAction;
use SolutionForest\InspireCms\Filament\TreeNode\Actions\ReorderContentItemAction;
use SolutionForest\InspireCms\Filament\TreeNode\Actions\SetDefaultContentPageAction;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Support\TreeNodes\Actions\Action as TreeNodeAction;
use SolutionForest\InspireCms\Support\TreeNodes\Actions\ActionGroup;
use SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer;

class ContentSidebar extends \SolutionForest\InspireCms\Support\TreeNodes\ModelExplorerComponent implements HasActions
{
    use InteractsWithActions;

    public array $redirectUrlParameters = [];

    public ?string $activeLocale = null;

    public ?string $pageName = null;

    public array $expandedModelExplorerItems = [];

    public array $cachedModelExplorerRecords = [];

    public bool $isExpandedSidebar = true;

    public bool $isSpaMode = true;

    public function mount()
    {
        if (filled($this->selectedModelItemKey)) {
            $this->refreshModelExplorerSidebar();
        }
        if (!isset($this->activeLocale)) { // set default locale if not set
            $this->activeLocale = Arr::first($this->getTranslatableLocales());
        }
    }

    //region Locale config
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
        $this->refreshModelExplorerSidebar();
        // dispatch event to page component
        $this->dispatch('changeActiveLocale', $locale);
    }
    //endregion Locale config

    public function modelExplorer(ModelExplorer $modelExplorer): ModelExplorer
    {
        $modelClass = InspireCmsConfig::getContentModelClass();
        $model = app($modelClass);
        $parentIdColumn = $model->getQualifiedParentKeyName();
        $rootLevelKey = $model->getRootLevelParentId();

        return $modelExplorer
            ->model($modelClass)
            ->parentColumnName($parentIdColumn)
            ->rootLevelKey($rootLevelKey)
            ->modifyQueryUsing(
                fn ($query) => $query
                    ->sortedByTree()
                    ->withCount([
                        'children',
                    ])
                    ->with([
                        'documentType',
                    ])
            )
            ->determineRecordLabelUsing(fn (Model | Content $record) => $record->title)
            ->determineRecordHasChildrenUsing(function (Model | Content $record) {

                if ($record->documentType?->show_as_table) {
                    return false;
                }

                return $record->children_count > 0;
            })
            ->mutuateNodeItemsUsing(function (array $item, Model | Content $record): array {

                // authorize user to view/edit the record
                $pageType = null;
                $resource = static::getResource();
                foreach (['edit', 'view'] as $action) {
                    $method = 'can' . ucfirst($action);
                    if ($resource::{$method}($record)) {
                        $pageType = $action;
                        break;
                    }
                }
                $item['pageType'] = $pageType;

                if (in_array('Spatie\Translatable\HasTranslations', class_uses_recursive($record))) {
                    $item['label'] = $record->getTranslations('title');
                    $item['fallbackLabel'] = $record->getTranslation('title', $record->getFallbackLocale());
                    if (blank($item['fallbackLabel'])) {
                        $item['fallbackLabel'] = collect($record->getTranslations('title'))->filter()->first();
                    }
                }

                $item['icon'] = $record->documentType?->icon;
                $item['documentTypeKey'] = $record->document_type_id;

                if ($record->display_status?->getName() !== 'publish') {
                    $item['extraAttributes']['class'] = [
                        '!text-gray-400',
                    ];
                }

                return $item;
            })
            ->actions([
                CreateContentItemAction::make(),
                ActionGroup::make([
                    ReorderContentItemAction::make('reorder_content_item'),
                    SetDefaultContentPageAction::make(),
                    DeleteContentItemAction::make(),
                ])->dropdown(false)->hidden(fn ($itemKey) => $itemKey === 'root'),
            ]);
    }

    protected function refreshModelExplorerSidebar(): void
    {
        $record = filled($this->selectedModelItemKey) ? $this->resolveSelectedModelItem($this->selectedModelItemKey) : null;

        if (! $record) {
            return;
        }

        if ($record->trashed()) {
            $this->expandedModelExplorerItems = [];

            return;
        }

        $this->selectedModelItem($record);

        $ancestorsAndSelf = collect($record->ancestorsAndSelf)->reverse()->values();
        foreach ($ancestorsAndSelf as $index => $item) {
            $this->cacheModelExplorerNodesOn($item->getParentId(), $index);
            if ($item->getKey() !== $record->getKey()) {
                $this->expandedModelExplorerItems[] = $item->getKey();
            }
        }
    }

    /**
     * @return null | Model & Content
     */
    protected function resolveSelectedModelItem(string | int $key): ?Model
    {
        if (in_array($key, ['root'])) {
            return null;
        }

        if (isset($this->cachedModelExplorerRecords[$key])) {
            return $this->cachedModelExplorerRecords[$key];
        }

        return $this->cachedModelExplorerRecords[$key] = $this->getModelExplorer()->findRecord($key);
    }

    protected function setSelectedModelItem(string | int | Model | null $record): void
    {
        if ($record) {

            /**
             * @var null | Model & Content $item
             */
            $item = $record instanceof Model ? $record : $this->resolveSelectedModelItem($record);

            if ($item?->parent?->documentType->show_as_table) {
                parent::setSelectedModelItem($item->parent);

                return;
            }
        }

        parent::setSelectedModelItem($item);

    }

    protected function getModelExplorerItemsFrom(string | int $parentKey, int $depth): array
    {
        $selectItem = $this->resolveSelectedModelItem($parentKey);

        if ($selectItem?->documentType->show_as_table) {
            return [];
        }

        return parent::getModelExplorerItemsFrom($parentKey, $depth);
    }

    protected function mutateCachedModelExplorerItemsBeforeGroup(array $items): array
    {
        foreach ($items as $parentKey => &$nodes) {
            foreach ($nodes as &$node) {
                if (! isset($node['pageType'])) {
                    continue;
                }

                $itemUrlParams = array_merge([
                    'record' => $node['key'],
                    'activeRelationManager' => 0,
                ], $this->redirectUrlParameters, [
                    'locale' => $this->activeLocale,
                ]);

                $node['link'] = FilamentResourceHelper::attemptToGetUrl(
                    static::getResource(),
                    $node['pageType'],
                    $itemUrlParams,
                    false
                );
                unset($node['pageType']);
            }
        }
        return $items;
    }

    public function getGroupedNodeItems()
    {
        $items = parent::getGroupedNodeItems();

        return array_merge([
            [
                'key' => 'root',
                'parentKey' => -1,
                'label' => __('inspirecms::inspirecms.root'),
                'hasChildren' => false,
                'depth' => -1,
                'link' => FilamentResourceHelper::attemptToGetUrl(static::getResource(), ['index'], $this->getRedirectUrlParameters(), false),
                'documentTypeKey' => null,
            ],
        ], $items);
    }

    public function render()
    {
        return view('inspirecms::livewire.content-sidebar', [
            'translatableLocale' => $this->activeLocale,
            'translatable' => filled($this->activeLocale),
            'modelExplorer' => $this->getModelExplorer(),
            'expandedItemsStateKey' => 'expandedModelExplorerItems',
        ]);
    }

    /**
     * @return class-string<\Filament\Resources\Resource>
     */
    protected static function getResource()
    {
        return InspireCmsConfig::get('filament.resources.page', PageResource::class);
    }

    protected function getRedirectUrlParameters()
    {
        return array_merge($this->redirectUrlParameters, [
            'locale' => $this->activeLocale,
        ]);
    }

    protected function configureSelectedModelItemFormAction(Actions\Action | TreeNodeAction $action): void
    {
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
                    ->parentDocumentType(fn ($itemKey) => data_get($this->getCacheModelItemNode($itemKey) ?? [], 'documentTypeKey'))
                    ->nodeTitleUsing(function ($itemKey, $livewire) {
                        $item = $livewire->getCacheModelItemNode($itemKey);

                        $itemLabel = $item['label'] ?? null;

                        $translatableLocale = method_exists($livewire, 'getActiveActionsLocale') ? $livewire->getActiveActionsLocale() : null;

                        if (! blank($translatableLocale) && $itemLabel && is_array($itemLabel)) {
                            $itemLabel = $itemLabel[$translatableLocale] ?? $item['fallbackLabel'] ?? null;
                        } elseif (is_array($itemLabel)) {
                            $itemLabel = reset($itemLabel);
                        }

                        return $itemLabel;
                    });

                break;
            case $action instanceof DeleteContentItemAction:
            case $action instanceof SetDefaultContentPageAction:

                $action
                    ->record(fn ($itemKey) => $this->resolveSelectedModelItem($itemKey))
                    ->successRedirectUrl(fn () => FilamentResourceHelper::attemptToGetUrl(static::getResource(), 'index', $this->getRedirectUrlParameters(), false));

                break;

            case $action instanceof ReorderContentItemAction:

                $action
                    ->record(fn ($itemKey) => ! blank($itemKey) ? $this->resolveSelectedModelItem($itemKey) : null)
                    ->nodeParentId(function (?Model $record) {
                        if (! $record instanceof Content) {
                            throw new \Exception('The provided record is not an instance of the Content model.');
                        }

                        $nestableTreeParentId = isset($record->nestable_tree_parent_id)
                            ? $record->nestable_tree_parent_id
                            : ($record->nestableTree?->parent_id ?? 0);

                        return $nestableTreeParentId;
                    })
                    ->successRedirectUrl(function () {
                        $pageName = $this->pageName ?? 'index';
                        if (filled($this->selectedModelItemKey) && in_array($pageName, ['edit', 'view'])) {
                            return FilamentResourceHelper::attemptToGetUrl(
                                static::getResource(),
                                $pageName,
                                ['record' => $this->selectedModelItemKey, ...$this->getRedirectUrlParameters()],
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
}
