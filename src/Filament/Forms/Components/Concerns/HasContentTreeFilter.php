<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\Concerns;

use Closure;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentTree\Filter\BaseFilter;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentTree\FilterCollection;

trait HasContentTreeFilter
{
    /**
     * @var array<array{0:string|BaseFilter|Closure,1:?string,2mixed}>
     */
    public array $filters = [];

    /**
     * @param array{0:string|BaseFilter|Closure,1:?string,2mixed} $filters
     * @param bool $merge
     */
    public function filter(array $filters, bool $merge = true): static
    {
        $this->filters = $merge ? array_merge($this->filters, $filters) : $filters;

        return $this;
    }

    /**
     * @param  string | BaseFilter | Closure  $key
     */
    public function whereKey($key): static
    {
        return $this->where('id', '==', $key);
    }

    /**
     * @param  string | BaseFilter | Closure  $key
     */
    public function whereKeyNot($key): static
    {
        return $this->whereNot('id', $key);
    }

    /**
     * @param  string | BaseFilter | Closure  $key
     */
    public function whereIn($key, $values): static
    {
        return $this->where($key, 'in', $values);
    }

    /**
     * @param  string | BaseFilter | Closure  $key
     */
    public function whereNotIn($key, $values): static
    {
        return $this->where($key, 'not in', $values);
    }

    /**
     * @param  string | BaseFilter | Closure  $key
     */
    public function whereNot($key, $value): static
    {
        return $this->where($key, 'not', $value);
    }

    /**
     * @param  string | BaseFilter | Closure  $key
     */
    public function where($key, $operator = null, $value = null): static
    {
        return $this->filter([
            [
                $key,
                $operator,
                $value,
            ],
        ], true);
    }

    public function getFilter(): FilterCollection
    {
        $items = array_map(function ($filter) {
            [$key, $operator, $value] = $filter;

            if (is_callable($key)) {
                $newFilter = $this->evaluate($key, [
                    'filter' => $filter,
                ]);

                if ($newFilter instanceof BaseFilter || is_array($newFilter)) {
                    return $newFilter;
                } else {
                    return null;
                }
            } 

            return $filter;

        }, $this->filters);

        $items = array_filter($items, function ($item) {
            return $item !== null;
        });

        return new FilterCollection($items);
    }
}
