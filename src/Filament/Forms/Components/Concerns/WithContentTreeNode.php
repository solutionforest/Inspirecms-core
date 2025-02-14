<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\InspireCms\InspireCmsConfig;

trait WithContentTreeNode
{
    use HasContentTreeFilter;

    public null | Closure | string $startNode = null;

    public bool $filteringByPermission = true;

    public function startNode(Closure | string $parentId): static
    {
        $this->startNode = $parentId;

        return $this;
    }

    public function getStartNode(): ?string
    {
        return $this->evaluate($this->startNode);
    }

    public function filteringByPermission(bool $condition = true): static
    {
        $this->filteringByPermission = $condition;

        return $this;
    }

    public function isFilteringByPermission(): bool
    {
        return $this->filteringByPermission;
    }

    /**
     * @return Builder
     */
    protected function getBaseEloquentQuery()
    {
        return InspireCmsConfig::getContentModelClass()::query();
    }

    /**
     * @return Builder
     */
    protected function getEloquentQuery()
    {
        $query = $this->getBaseEloquentQuery();

        $startNode = $this->getStartNode();
        $filter = $this->getFilter();

        if ($startNode != null || $filter->isNotEmpty()) {

            if ($filter->isNotEmpty()) {

                $this->getFilter()->applyOnQuery($query);

            }

            if ($startNode != null) {
                $query->whereParent($startNode);
            }
        }

        return $query;
    }

    protected function filterStateBeforeDehydrating(array $state): array
    {
        $keys = array_filter($state, fn ($value) => $value !== null);

        $startNode = $this->getStartNode();
        $filter = $this->getFilter();

        if ($startNode != null || $filter->isNotEmpty()) {

            $filterRecordKeys = $this->getEloquentQuery()->whereKey($keys)->pluck($query->getModel()->getKeyName())->all();

            return $filterRecordKeys;
        }

        return $keys;
    }
}
