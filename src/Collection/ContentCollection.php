<?php

namespace SolutionForest\InspireCms\Collection;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ContentCollection extends Collection
{
    public function toDto(...$args)
    {
        $items = $this->map(fn ($item) => 
            match (true) {
                $item instanceof \SolutionForest\InspireCms\Models\Contracts\Content => $item->toDto(...$args),
                default => $item,
            })
            ->toArray();
        return new static($items);
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
     * @throws \InvalidArgumentException
     */
    public function paginate($perPage = null, $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $total = count($this->items);

        $perPage = $this->retrieveItemsPerPage($perPage);

        $items = $total > 0 ? $this->forPage($page, $perPage) : new static();

        return $this->paginator($items, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
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

        $items = $total > 0 ? $this->forPage($page, $perPage) : new static();

        return $this->simplePaginator($items, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * Create a new length-aware paginator instance.
     *
     * @param  \Illuminate\Support\Collection  $items
     * @param  int  $total
     * @param  int  $perPage
     * @param  int  $currentPage
     * @param  array  $options
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected function paginator($items, $total, $perPage, $currentPage, $options)
    {
        return Container::getInstance()->makeWith(LengthAwarePaginator::class, compact(
            'items', 'total', 'perPage', 'currentPage', 'options'
        ));
    }

    /**
     * Create a new simple paginator instance.
     *
     * @param  \Illuminate\Support\Collection  $items
     * @param  int  $perPage
     * @param  int  $currentPage
     * @param  array  $options
     * @return \Illuminate\Pagination\Paginator
     */
    protected function simplePaginator($items, $perPage, $currentPage, $options)
    {
        return Container::getInstance()->makeWith(Paginator::class, compact(
            'items', 'perPage', 'currentPage', 'options'
        ));
    }

    protected static function getDtoRelations(): array
    {
        return [
            'documentType.fields.group',
            'documentType.templates',
            'webSetting',
            'publishedVersions',
            'templates',
        ];
    }

    /**
     * @param null | int | Closure $perPage
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
