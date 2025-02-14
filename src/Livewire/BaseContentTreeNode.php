<?php

namespace SolutionForest\InspireCms\Livewire;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Lazy;
use SolutionForest\InspireCms\Helpers\ContentHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer;
use SolutionForest\InspireCms\Support\TreeNodes\ModelExplorerComponent;

#[Lazy]
abstract class BaseContentTreeNode extends ModelExplorerComponent
{
    public array $cachedModelExplorerRecords = [];

    public bool $filterByPermission = true;

    public function placeholder()
    {
        return <<<'HTML'
            <div>Loading...</div>
        HTML;
    }

    public function modelExplorer(ModelExplorer $modelExplorer): ModelExplorer
    {
        $modelClass = static::getModel();
        $model = app($modelClass);
        $parentIdColumn = $model->getQualifiedParentKeyName();

        return $modelExplorer
            ->model($modelClass)
            ->parentColumnName($parentIdColumn)
            ->rootLevelKey($this->getModelExplorerRootLevelId())
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->withCount([
                        'children',
                    ])
                    ->with([
                        // To expand its ancestors
                        'ancestorsAndSelf' => fn ($q) => $q->breadthFirst(),
                        'documentType',
                        'nestableTree',
                    ])
                    ->sortedByTree()
            )
            ->determineItemDepthUsing(function (Model | Content $record, $parentKey) {
                if ($parentKey == $this->getModelExplorerRootLevelId()) {
                    return 0;
                }

                $parentDepth = collect($this->cachedModelExplorerItems)
                    ->flatten(1)
                    ->firstWhere('key', $parentKey)['depth'] ?? -1;

                return $parentDepth + 1;
            })
            ->determineItemTitleUsing(fn (Model | Content $record) => $record->title)
            ->determineItemHasChildrenUsing(fn (Model | Content $record) => $record->children_count > 0);
    }

    public function getGroupedNodeItems()
    {
        $items = parent::getGroupedNodeItems();

        if (! $this->filterByPermission) {
            return $items;
        }

        $idsOrBool = ContentHelper::getAccessibleContentIds();

        if ($idsOrBool === true) {
            return $items;
        }

        return collect($items)
            ->filter(function (array $item) use ($idsOrBool) {
                $key = $item['key'];
                $parentKey = $item['parentKey'];

                if (in_array($key, $idsOrBool)) {
                    return true;
                }

                if ($parentKey == $this->getModelRootLevelParentId()) {
                    return true;
                }

                if (in_array($parentKey, $idsOrBool)) {
                    return true;
                }

                return false;
            })
            ->values()
            ->all();
    }

    protected function resolveSelectedModelItems(...$keys)
    {
        $keys = collect($keys)->flatten()->unique()->all();

        $result = collect($keys)->mapWithKeys(fn ($key) => [$key => $this->getCachedModelExplorerRecord($key)])->all();

        $missingKeys = collect($keys)
            ->filter(fn ($key) => ($result[$key] ?? null) == null)
            ->filter(fn ($key) => (is_string($key) || is_int($key)) && $this->isValidSelectableModelItemKey($key))
            ->all();

        $recordsToCache = count($missingKeys) > 0 ? parent::resolveSelectedModelItems($missingKeys) : collect();

        if ($recordsToCache != null) {
            foreach ($recordsToCache as $record) {

                $key = $record->getKey();

                $this->cachedModelExplorerRecord(key: $key, record: $record);

                $result[$key] = $record;
            }
        }

        return collect($result)->filter();
    }

    /**
     * @param  string  $key
     * @param  null | Model & Content  $record
     */
    protected function cachedModelExplorerRecord($key, $record)
    {
        if (! isset($this->cachedModelExplorerRecords[$key])) {
            return $this->cachedModelExplorerRecords[$key] = $record;
        }
    }

    protected function getCachedModelExplorerRecord($key)
    {
        return $this->cachedModelExplorerRecords[$key] ?? null;
    }

    /**
     * @return class-string<Model & Content>
     */
    protected static function getModel()
    {
        return InspireCmsConfig::getContentModelClass();
    }

    protected function getModelRootLevelParentId(): int | string
    {
        return app(static::getModel())->getRootLevelParentId();
    }

    protected function getModelExplorerRootLevelId(): int | string
    {
        return $this->getModelRootLevelParentId();
    }

    protected function getAncestorsFor(...$keys): array
    {
        return collect($this->resolveSelectedModelItems($keys))
            ->map(function (Model | Content $self) {

                return collect($self->ancestorsAndSelf)
                    ->reverse()
                    ->values()
                    ->keyBy(fn (Model | Content $item) => $item->getKey())
                    ->except($self->getKey())
                    ->all();
            })
            ->all();
    }
}
