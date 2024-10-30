<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Concerns;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Actions\CreateContentAction;
use SolutionForest\InspireCms\Filament\Actions\DeleteContentAction;
use SolutionForest\InspireCms\Filament\Actions\LinkToParentAction;
use SolutionForest\InspireCms\Filament\Actions\ReorderContentAction;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages\BaseContentCreatePage;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Support\InspireCmsConfig;
use SolutionForest\InspireCms\Support\TreeNodes\Concerns\InteractsWithModelExplorer;
use SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer;

trait ContentPageTrait
{
    use InteractsWithModelExplorer {
        modelExplorer as protected traitModelExplorer;
        setSelectedModelItem as protected traitSetSelectedModelItem;
        getModelExplorerItemsFrom as protected traitGetModelExplorerItemsFrom;
    }

    public array $expandedModelExplorerItems = [];

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
        $parentIdColumn = $model->getQualifiedNestableParentIdColumn();
        $rootLevelKey = $model->getNestableRootValue();

        return $modelExplorer
            ->model($modelClass)
            ->parentColumnName($parentIdColumn)
            ->rootLevelKey($rootLevelKey)
            ->modifyQueryUsing(
                fn ($query) => $query
                    ->sortedByTree()
                    ->withNestableTreeParentId()
                    ->withCount([
                        'children',
                    ])
                    ->with([
                        'documentType',
                    ])
            )
            ->determineRecordLabelUsing(fn ($record) => $record->title)
            ->determineRecordHasChildrenUsing(function ($record) {
                if ($record->documentType->isShowChildrenAsTable()) {
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
                ],
            ], $items))
            ->mutuateNodeItemsUsing(function (array $item, Model $record) {
                $item['link'] = FilamentResourceHelper::attemptToGetUrl(static::getResource(), ['edit', 'view'], [
                    'record' => $record,
                    'activeRelationManager' => 0,
                ], true);

                return $item;
            })
            ->actions([
                CreateContentAction::make('create_content_item'),
                LinkToParentAction::make('item_link_to_parent'),
                ReorderContentAction::make('reorder_content_item'),
                DeleteContentAction::make('delete_content_item'),
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

        return $this->getModelExplorer()->findRecord($key);
    }

    protected function configureSelectedModelItemFormAction(Actions\Action $action): void
    {
        switch (true) {
            case $action instanceof CreateContentAction:

                $action
                    ->color('primary')
                    ->parentContentKey(function (array $arguments) {
                        $parent = $arguments['key'] ?? null;

                        if (in_array($parent, ['root'])) {
                            $parent = null;
                        }

                        return $parent;
                    });

                break;
            case $action instanceof DeleteContentAction:

                $action
                    ->record(fn (array $arguments) => $this->resolveSelectedModelItem($arguments['key']))
                    ->hidden(fn (array $arguments) => (isset($arguments['key']) && $arguments['key'] === 'root') || ! isset($arguments['key']))
                    ->successRedirectUrl(fn () => FilamentResourceHelper::attemptToGetUrl(static::getResource(), 'index', [], false));

                break;

            case $action instanceof LinkToParentAction:

                $action
                    ->record(fn (array $arguments) => isset($arguments['key']) ? $this->resolveSelectedModelItem($arguments['key']) : null)
                    ->hidden(fn (array $arguments) => (isset($arguments['key']) && $arguments['key'] === 'root') || ! isset($arguments['key']));

                break;
            case $action instanceof ReorderContentAction:

                $action
                    ->record(fn (array $arguments) => isset($arguments['key']) ? $this->resolveSelectedModelItem($arguments['key']) : null)
                    ->hidden(fn (array $arguments) => (isset($arguments['key']) && $arguments['key'] === 'root') || ! isset($arguments['key']))
                    ->nodeParentId(function (array $arguments, ?Model $record) {
                        // find from argument
                        if (is_null($record)) {
                            $record = (isset($arguments['parent']) ? InspireCmsConfig::getContentModelClass()::find($arguments['parent']) : null)
                                // throw error if still null
                                ?? throw new \Exception('Record not found for the given parent ID.');
                        }
                        if (! $record instanceof Content) {
                            throw new \Exception('The provided record is not an instance of the Content model.');
                        }

                        return $record->nestable_tree_parent_id;
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

        $ancestors = collect($record->ancestors())->push($record);
        foreach ($ancestors as $index => $ancestor) {
            $this->getModelExplorerNodes($ancestor->parent_id, $index);
            if ($ancestor->getKey() !== $record->getKey()) {
                $this->expandedModelExplorerItems[] = $ancestor->getKey();
            }
        }
    }
    //endregion

    public function getSubNavigation(): array
    {
        return [];
    }
}
