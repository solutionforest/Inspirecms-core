<?php

namespace SolutionForest\InspireCms\Services;

use Closure;
use Illuminate\Support\Collection;

/**
 * Interface IndexSearchService
 *
 * @template T of \Illuminate\Database\Eloquent\Model
 */
interface IndexSearchServiceInterface
{
    /**
     * Searches for a single item based on the provided keyword.
     *
     * @param  string  $keyword  The keyword to search for.
     * @return ?T The result of the search, or null if no match is found.
     */
    public function searchOne(string $keyword, ?Closure $searchBuilder = null, ?Closure $queryBuilder = null);

    /**
     * Searches for the given keyword.
     *
     * @param  string  $keyword  The keyword to search for.
     * @return Collection<T> The result of the search operation.
     */
    public function search(string $keyword, ?Closure $searchBuilder = null, ?Closure $queryBuilder = null);
}
