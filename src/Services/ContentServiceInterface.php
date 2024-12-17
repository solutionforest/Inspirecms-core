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
     * @param  string  $id  The ID of the web page to find.
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
     * Find content by its real path.
     *
     * @param  string  $slugPath  The slug path of the content.
     * @param  array  $withRelations  Optional. An array of relations to load with the content.
     * @return mixed The content found by the given slug path, or null if not found.
     */
    public function findByRealPath(string $slugPath, $withRelations = []);

    /**
     * Retrieve content by its real path.
     *
     * @param  string  $slugPath  The slug path of the content to retrieve.
     * @param  array  $withRelations  The relations to load with the content.
     * @return Collection<string,TResult> The content associated with the given slug path.
     */
    public function getByRealPath(string $slugPath, $withRelations = []);

    /**
     * Retrieve the content under the content with the provided real path.
     *
     * @param  string  $slugPath  The slug path used to identify the parent content.
     * @param  int|null  $limit  The maximum number of content items to retrieve, or null for unlimited.
     * @param  array  $withRelations  The relations to load with the content.
     * @return Collection<TResult>
     */
    public function getUnderRealPath(string $slugPath, $limit = null, $withRelations = []);
}
