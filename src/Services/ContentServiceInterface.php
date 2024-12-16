<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Support\Collection;

/**
 * @template TResult of \Illuminate\Database\Eloquent\Model|\SolutionForest\InspireCms\Models\Contracts\Content
 */
interface ContentServiceInterface
{
    /**
     * Find a published web page by its ID.
     *
     * @param  string|int  $id  The ID of the web page to find.
     * @return ?TResult The content item if found, or null if not found.
     */
    public function findPublishedWebPageById($id);

    /**
     * Find published content by their IDs.
     *
     * @param  string  ...$ids  The IDs of the content to find.
     * @return Collection<TResult> The published content corresponding to the given IDs.
     */
    public function findPublishedContentByIds(...$ids);

    /**
     * Find content by their IDs.
     *
     * @param  string  ...$ids  The IDs of the content to find.
     * @return Collection<TResult> The content corresponding to the given IDs.
     */
    public function findContentByIds(...$ids);

    /**
     * Find the default web page.
     *
     * @return ?TResult The index web page.
     */
    public function findDefaultWebPage();

    /**
     * Find a web page by its slug path.
     *
     * @param  string  $slugPath  The slug path of the web page.
     * @return ?TResult The web page object or null if not found.
     */
    public function findWebPageBySlugPath(string $slugPath);

    /**
     * Retrieve content by its slug path.
     *
     * @param  string  $slugPath  The slug path of the content to retrieve.
     * @return Collection<string,TResult> The content associated with the given slug path.
     */
    public function getBySlugPath(string $slugPath);
}
