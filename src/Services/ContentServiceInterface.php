<?php

namespace SolutionForest\InspireCms\Services;

use Closure;
use Illuminate\Support\Collection;

/**
 * @template TResult of \Illuminate\Database\Eloquent\Model|\SolutionForest\InspireCms\Models\Contracts\Content
 */
interface ContentServiceInterface
{
    /**
     * Search for content based on a keyword.
     *
     * @param  string  $keyword  The keyword to search for.
     * @param  Closure|null  $builderCallback  Optional callback to modify the query builder.
     * @param  Closure|null  $queryCallback  Optional callback to modify the query.
     * @return TResult The search results.
     */
    public function search($keyword, ?Closure $builderCallback = null, ?Closure $queryCallback = null);

    /**
     * Search for a single content item based on the provided keyword.
     *
     * @param  string  $keyword  The keyword to search for.
     * @param  Closure|null  $builderCallback  Optional callback to modify the query builder.
     * @param  Closure|null  $queryCallback  Optional callback to modify the query.
     * @return TResult The result of the search query.
     */
    public function searchOne($keyword, ?Closure $builderCallback = null, ?Closure $queryCallback = null);

    /**
     * Find the index web page.
     *
     * @return TResult The index web page.
     */
    public function findIndexWebPage();

    /**
     * Retrieve content by its slug path.
     *
     * @param  string  $slugPath  The slug path of the content to retrieve.
     * @return Collection<string,TResult> The content associated with the given slug path.
     */
    public function getBySlugPath(string $slugPath);
}
