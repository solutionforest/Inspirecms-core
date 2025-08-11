<?php

namespace SolutionForest\InspireCms\Collection;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use InvalidArgumentException;
use SolutionForest\InspireCms\Models\Contracts\Content;
use Staudenmeir\LaravelAdjacencyList\Eloquent\Collection as BaseCollection;

class ContentCollection extends BaseCollection
{
    protected $paginator = null;

    public function toDto(...$args)
    {
        $items = $this
            ->map(fn ($item) => match (true) {
                $item instanceof Content => $item->toDto(...$args),
                default => $item,
            })
            ->reject(fn ($item) => is_null($item))
            ->toArray();

        $collection = static::make($items);

        if ($this->paginator != null) {
            return $this->paginator->setCollection(
                $collection->setPaginator($this->paginator)
            );
        }

        return $collection;
    }

    public function setPaginator($paginator)
    {
        $this->paginator = $paginator;

        return $this;
    }

    /**
     * Paginate the items.
     *
     * @param  int|null  $perPage
     * @param  array|string  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     * @throws InvalidArgumentException
     */
    public function paginate($perPage = null, $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $total = count($this->items);

        $perPage = $this->retrieveItemsPerPage($perPage);

        $items = $total > 0 ? $this->forPage($page, $perPage) : new static;

        $paginator = $this->paginator($items, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);

        $paginator->setCollection($items->setPaginator($paginator));

        return $paginator;
    }

    /**
     * Paginate the items into a simple paginator.
     *
     * @param  int|null  $perPage
     * @param  array|string  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function simplePaginate($perPage = null, $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $total = count($this->items);

        $perPage = $this->retrieveItemsPerPage($perPage);

        $items = $total > 0 ? $this->forPage($page, $perPage) : new static;

        $paginator = $this->simplePaginator($items, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);

        $paginator->setCollection($items->setPaginator($paginator));

        return $paginator;
    }

    /**
     * Create a new length-aware paginator instance.
     *
     * @param  \Illuminate\Support\Collection  $items
     * @param  int  $total
     * @param  int  $perPage
     * @param  int  $currentPage
     * @param  array  $options
     * @return LengthAwarePaginator
     */
    protected function paginator($items, $total, $perPage, $currentPage, $options)
    {
        return Container::getInstance()->makeWith(LengthAwarePaginator::class, compact(
            'items',
            'total',
            'perPage',
            'currentPage',
            'options'
        ));
    }

    /**
     * Create a new simple paginator instance.
     *
     * @param  \Illuminate\Support\Collection  $items
     * @param  int  $perPage
     * @param  int  $currentPage
     * @param  array  $options
     * @return Paginator
     */
    protected function simplePaginator($items, $perPage, $currentPage, $options)
    {
        return Container::getInstance()->makeWith(Paginator::class, compact(
            'items',
            'perPage',
            'currentPage',
            'options'
        ));
    }

    /**
     * @param  null | int | Closure  $perPage
     * @return int
     */
    protected function retrieveItemsPerPage($perPage = null)
    {
        $default = 10;

        $total = count($this->items);

        if ($perPage instanceof Closure) {
            $perPage = $perPage($total);
        }

        if (is_null($perPage)) {
            $perPage = $default;
        }

        return $perPage;
    }
}
