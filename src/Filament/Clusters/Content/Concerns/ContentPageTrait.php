<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Concerns;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages\BaseContentCreatePage;
use SolutionForest\InspireCms\Filament\TreeNode\Actions\CreateContentItemAction;
use SolutionForest\InspireCms\Filament\TreeNode\Actions\DeleteContentItemAction;
use SolutionForest\InspireCms\Filament\TreeNode\Actions\ReorderContentItemAction;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Support\TreeNodes\Actions\Action as TreeNodeAction;
use SolutionForest\InspireCms\Support\TreeNodes\Actions\ActionGroup;
use SolutionForest\InspireCms\Support\TreeNodes\Concerns\InteractsWithModelExplorer;
use SolutionForest\InspireCms\Support\TreeNodes\Contracts\HasModelExplorer;
use SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer;

trait ContentPageTrait
{
    use InteractsWithModelExplorer {
        modelExplorer as protected traitModelExplorer;
        setSelectedModelItem as protected traitSetSelectedModelItem;
        getModelExplorerItemsFrom as protected traitGetModelExplorerItemsFrom;
    }

    public array $expandedModelExplorerItems = [];

    public array $cachedModelExplorerRecords = [];

    public function mountContentPageTrait()
    {
        if (! $this instanceof ListRecords) {
            $this->refreshModelExplorerSidebar();
        }
    }

    //region Model Explorer
    public function modelExplorer(ModelExplorer $modelExplorer): ModelExplorer
    {
        $modelClass = $this->getModel();
        $model = new $modelClass;
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
            ->determineRecordLabelUsing(fn ($record) => $record->title)
            ->determineRecordHasChildrenUsing(function ($record) {
                if ($record->documentType?->isShowChildrenAsTable()) {
                    return false;
                }

                return $record->children_count > 0;
            })
            ->mutuateRootNodeItemsUsing(fn ($items) => array_merge([
                [
                    'key' => 'root',
                    'parentKey' => $this->getModelExplorer()->getRootLevelKey(),
                    'label' => __('inspirecms::inspirecms.root'),
                    'hasChildren' => false,
                    'depth' => 0,
                    'icon' => 'heroicon-o-home',
                    'link' => FilamentResourceHelper::attemptToGetUrl(static::getResource(), ['index'], [], false),
                    'documentTypeKey' => null,
                ],
            ], $items))
            ->mutuateNodeItemsUsing(function (array $item, Model $record) {
                $item['link'] = FilamentResourceHelper::attemptToGetUrl(static::getResource(), ['edit', 'view'], [
                    'record' => $record,
                    'activeRelationManager' => 0,
                ], true);

                if (in_array('Spatie\Translatable\HasTranslations', class_uses_recursive($record))) {
                    $item['label'] = $record->getTranslations('title');
                    $item['fallbackLabel'] = $record->getTranslation('title', $record->getFallbackLocale());
                }

                $item['documentTypeKey'] = $record->document_type_id;

                return $item;
            })
            ->actions([
                CreateContentItemAction::make(),
                ActionGroup::make([
                    ReorderContentItemAction::make('reorder_content_item'),
                    DeleteContentItemAction::make(),
                ])->dropdown(false)->hidden(fn ($itemKey) => $itemKey === 'root'),
            ]);
    }

    protected function getModelExplorerItemsFrom(string | int $parentKey, int $depth): array
    {
        $selectItem = $this->resolveSelectedModelItem($parentKey);

        if ($selectItem?->documentType->isShowChildrenAsTable()) {
            return [];
        }

        return $this->traitGetModelExplorerItemsFrom($parentKey, $depth);
    }

    protected function setSelectedModelItem(string | int | Model | null $record): void
    {
        if ($record) {

            $item = $record instanceof Model ? $record : $this->resolveSelectedModelItem($record);

            if ($item?->parent?->documentType->isShowChildrenAsTable()) {
                $this->traitSetSelectedModelItem($item->parent);

                return;
            }
        }

        $this->traitSetSelectedModelItem($item);

    }

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
                    ->parentDocumentType(fn ($itemKey, HasModelExplorer $livewire) => data_get($livewire->getCacheModelItemNode($itemKey) ?? [], 'documentTypeKey'))
                    ->nodeTitleUsing(function ($itemKey, $livewire) {
                        $item = $livewire->getCacheModelItemNode($itemKey);

                        $itemLabel = $item['label'] ?? null;

                        $translatableLocale = $livewire->getActiveActionsLocale();

                        if (! blank($translatableLocale) && $itemLabel && is_array($itemLabel)) {
                            $itemLabel = $itemLabel[$translatableLocale] ?? $item['fallbackLabel'] ?? null;
                        } elseif (is_array($itemLabel)) {
                            $itemLabel = reset($itemLabel);
                        }

                        return $itemLabel;
                    });

                break;
            case $action instanceof DeleteContentItemAction:

                $action
                    ->record(fn ($itemKey) => $this->resolveSelectedModelItem($itemKey))
                    ->successRedirectUrl(fn () => FilamentResourceHelper::attemptToGetUrl(static::getResource(), 'index', [], false));

                break;

            case $action instanceof ReorderContentItemAction:

                $action
                    ->record(fn ($itemKey) => ! blank($itemKey) ? $this->resolveSelectedModelItem($itemKey) : null)
                    ->nodeParentId(function (?Model $record) {
                        if (! $record instanceof Content) {
                            throw new \Exception('The provided record is not an instance of the Content model.');
                        }

                        return $record->nestableTree?->parent_id ?? 0;
                    })
                    ->successRedirectUrl(function () {
                        if ($this instanceof EditRecord || $this instanceof ViewRecord) {
                            return $this->getUrl(['record' => $this->getRecord()]);
                        }

                        if ($this instanceof ListRecords || $this instanceof CreateRecord) {
                            return $this->getUrl();
                        }

                        return null;
                    });

                break;
            default:
                break;
        }

        $this->cacheAction($action);

    }

    protected function refreshModelExplorerSidebar(): void
    {
        $record = $this instanceof BaseContentCreatePage ?
            $this->getParentRecord() :
            $this->getRecord();

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
    //endregion

    public function getSubNavigation(): array
    {
        return [];
    }
}
