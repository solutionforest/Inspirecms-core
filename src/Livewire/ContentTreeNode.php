<?php

namespace SolutionForest\InspireCms\Livewire;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Livewire\WithPagination;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentTree\FilterCollection;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer;

class ContentTreeNode  extends BaseContentTreeNode
{
    use WithPagination;

    public ?string $startNode = null;

    public ?string $search = null;

    public ?string $modelable = null;

    public array $limits = [];

    public bool $isDisabled = true;

    public int | string $perPage = 10;

    public FilterCollection $filter;

    public function isFilteringBySearch(): bool
    {
        return filled($this->search);
    }

    public function render()
    {
        return view('inspirecms::livewire.content-tree-node', [
            'items' => $this->isFilteringBySearch() ? $this->getSearchRecords() : $this->getGroupedNodeItems(),
            'pageOptions' => [5, 10, 20, 50, 100, 'all'],
        ]);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function modelExplorer(ModelExplorer $modelExplorer): ModelExplorer
    {
        return parent::modelExplorer($modelExplorer)
            ->minSelectItem($this->limits['min'] ?? null)
            ->maxSelectItem($this->limits['max'] ?? null)
            ->modifyQueryUsing(fn (Builder $query) => $this->modifyModelExplorerQuery($query))
            ->determineItemIsDisabledUsing(function (Model | Content $record) {
                if ($this->isDisabled) {
                    return true;
                }

                if ($this->filter?->isNotEmpty() && ! $this->filter->applyRecordFilter($record)) {
                    return true;
                }

                return false;
            })
            ->determineItemHasChildrenUsing(function (Model | Content $record) {
                if (isset($record->children_count)) {
                    return $record->children_count > 0;
                }
                return $record->ancestorsAndSelf->last()?->children_count > 0;
            })
            ->determineItemDescriptionUsing(fn (Model | Content $record) => $record->slug);
    }

    protected function getSearchRecords()
    {
        if (!$this->isFilteringBySearch()) {
            return new LengthAwarePaginator([], 0, $this->perPage);
        }

        $modelExplorer = $this->getModelExplorer();

        $baseQuery = $modelExplorer->getModelExplorerQuery()
            ->where('slug', 'like', "%{$this->search}%");
            
        if ($this->filter?->isNotEmpty()) {
            $this->filter->applyOnQuery($baseQuery);
        }
            
        $records = $baseQuery->paginate(perPage: $this->perPage, page: $this->getPage());

        $records->tap(function ($paginator) use ($modelExplorer) {
            $items = $modelExplorer->parseAsItems($paginator->getCollection(), $this->getModelExplorerRootLevelId());
            $paginator->setCollection($items);
            return $paginator;
        });

        return $records;
    }

    protected function getModelExplorerItemsFrom(string | int $parentKey): array
    {
        if (isset($this->cachedModelExplorerItems[$parentKey])) {
            return $this->cachedModelExplorerItems[$parentKey];
        }

        $modelExplorer = $this->getModelExplorer();

        // filter records
        if ($this->filter?->isNotEmpty()) {

            $constraint  = function ($query) {
    
                if (filled($this->startNode)) {
                    $query->whereParent($this->startNode);
                }

                $this->filter->applyOnQuery($query);
    
                return $query;
            };
            

            $tree = $this->modifyModelExplorerQuery($modelExplorer->getModel()::treeOf($constraint))->get();

            $groupedByParentKey = collect($tree)
                ->flatMap(fn ($r) => $r->ancestorsAndSelf)
                ->groupBy(fn ($r) => $r->parent_id)
                ->when(filled($this->startNode), fn (Collection $collection) => $collection->only($this->startNode))
                ->map(function ($records) {
                    return collect($records)
                        ->sortBy(fn ($record) => $record->nestableTree?->_lft)
                        ->values();
                });
                
            foreach ($groupedByParentKey as $itemParentKey => $records) {
                
                $nodeItems = $this->mutuateModelExplorerNodes($records, $itemParentKey);
                
                $this->cacheModelItemNode($itemParentKey, $nodeItems);

                if ($parentKey == $itemParentKey) {
                    $items = $nodeItems;
                }
            }

            if (!isset($items)) {
                $items = [];
            }
    
        } else {

            $records = $modelExplorer->getRecordsFrom($parentKey);

            $items = $this->mutuateModelExplorerNodes($records, $parentKey);
        }

        if ($parentKey === $modelExplorer->getRootLevelKey()) {
            $items = $modelExplorer->mutuateRootNodeItems($items);
        }

        return $items;
    }

    protected function getModelExplorerRootLevelId(): int | string
    {
        if (filled($this->startNode)) {
            return $this->startNode;
        }

        return parent::getModelExplorerRootLevelId();
    }

    protected function mutateCachedModelExplorerItemsBeforeGroup(array $items): array
    {
        $result = [];

        foreach ($items as $parentKey => $list) {
            foreach ($list as $item) {
                $itemParentKey = $item['parentKey'];
                $itemKey = $item['key'];

                $targetParentKey = $itemParentKey == $parentKey ? $parentKey : $itemParentKey;

                if (in_array($itemKey, Arr::pluck($result[$targetParentKey] ?? [], 'key'))) {
                    continue;
                }

                $result[$targetParentKey][] = $item;
            }
        }

        return $result;
    }

    private function modifyModelExplorerQuery(Builder $query)
    {
        return $query
            ->with([
                'ancestorsAndSelf' => fn ($q) => $q
                    ->withCount('children')
                    ->with([
                        'nestableTree',
                    ])
                    ->breadthFirst(),
            ]);
    }

    protected function expandParentModelItemIfSelected(array $keys)
    {
        if ($this->isFilteringBySearch()) {
            return;
        }

        if (filled($this->startNode)) {
            return;
        }

        return parent::expandParentModelItemIfSelected($keys);
    }
}
