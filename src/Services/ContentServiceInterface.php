<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\Content;

/**
 * Interface ContentService
 *
 * @extends IndexSearchServiceInterface<Model|\SolutionForest\InspireCms\Models\Contracts\Content>
 */
interface ContentServiceInterface extends IndexSearchServiceInterface
{
    /**
     * Find a content item by its unique identifier.
     *
     * @param  int  $id  The unique identifier of the content item.
     * @return null|Model|Content The content item associated with the given identifier.
     */
    public function findById(int $id);

    /**
     * Find content by its slug.
     *
     * @param  string  $slug  The slug of the content to find.
     * @return null|Model|Content The content associated with the given slug.
     */
    public function findBySlug(string $slug);

    /**
     * Finds a page by its full slug.
     *
     * @param  string  $fullSlug  The full slug of the page to find.
     * @return mixed The page object if found, or null if not found.
     */
    public function findPage(string $fullSlug);
}
