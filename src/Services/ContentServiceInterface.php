<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Collection\ContentCollection;

/**
 * @template TResult of \Illuminate\Database\Eloquent\Model | \SolutionForest\InspireCms\Models\Contracts\Content
 * @template TTemplate of \Illuminate\Database\Eloquent\Model | \SolutionForest\InspireCms\Models\Contracts\Template
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
     * Find published content by their ID.
     *
     * @param  string  $id  The ID of the content to find.
     * @return ?TResult The content item if found, or null if not found.
     */
    public function findPublishedContentById($id);

    /**
     * Find published content by their IDs.
     *
     * @param  string  ...$ids  The IDs of the content to find.
     * @return ContentCollection<TResult> The published content corresponding to the given IDs.
     */
    public function findPublishedContentByIds(...$ids);

    /**
     * Find the web page and language ID by the default URL segment route.
     *
     * @param string $urlSegment The URL segment to search for.
     * @return array{0:?TResult,1:null|int} The content and language ID if found, or null if not found.
     */
    public function findWebPageAndLangIdByDefaultRoute($urlSegment);

    /**
     * Find the web page and language ID by the given route pattern.
     *
     * @param string $routePattern The route pattern to search for.
     * @return array{0:?TResult,1:null|int} The content and language ID if found, or null if not found.
     */
    public function findWebPageAndLangIdByRoutePattern($routePattern);

    /**
     * Find content by its path.
     *
     * @param  string  $path
     * @return ?TResult 
     */
    public function findByRealPath(string $path);

    /**
     * Retrieve content by its paths.
     *
     * @param  string[]  $paths 
     * @param  array  $withRelations  The relations to load with the content.
     * @return ContentCollection<string,TResult>
     */
    public function getByRealPath($paths, $withRelations = []);

    /**
     * Retrieve the content under the content with the provided real path.
     *
     * @param  string  $path  
     * @param  int|null  $limit  The maximum number of content items to retrieve, or null for unlimited.
     * @param  array  $withRelations  The relations to load with the content.
     * @return ContentCollection<TResult>
     */
    public function getUnderRealPath(string $path, $limit = null, $withRelations = []);

    /**
     * Find content by slug under the its path.
     *
     * @param  string  $path  
     * @param  string  $slug  The slug of the content to find.
     * @return null | TResult 
     */
    public function findBySlugUnderRealPath(string $path, string $slug);

    /**
     * Get the default template for the given content.
     *
     * @param  TResult  $content  The content for which to get the default template.
     * @return null | (\Illuminate\Database\Eloquent\Model & \SolutionForest\InspireCms\Models\Contracts\Template) The default template for the given content.
     */
    public function getDefaultTemplateFor($content);

    /**
     * Retrieve templates for the given content.
     *
     * @param  TResult  $content  The content for which to retrieve templates.
     * @return Collection<string,TTemplate> The list of templates associated with the content.
     */
    public function getTemplatesFor($content);

    /**
     * Retrieves the template for the given content and template slug.
     *
     * @param  TResult  $content  The content for which the template is being retrieved.
     * @param  string  $templateSlug  The slug identifier for the template.
     * @return null | TTemplate The template associated with the given content and template slug.
     */
    public function getTemplateFor($content, $templateSlug);
}
