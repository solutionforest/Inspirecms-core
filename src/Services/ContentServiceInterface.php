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
     * @param  string  $realPath  The slug path of the content.
     * @param  array  $withRelations  Optional. An array of relations to load with the content.
     * @return ?TResult The content found by the given slug path, or null if not found.
     */
    public function findByRealPath(string $realPath, $withRelations = []);

    /**
     * Retrieve content by its real path.
     *
     * @param  string  $realPath  The slug path of the content to retrieve.
     * @param  array  $withRelations  The relations to load with the content.
     * @return ContentCollection<string,TResult> The content associated with the given slug path.
     */
    public function getByRealPath(string $realPath, $withRelations = []);

    /**
     * Retrieve the content under the content with the provided real path.
     *
     * @param  string  $realPath  The slug path used to identify the parent content.
     * @param  int|null  $limit  The maximum number of content items to retrieve, or null for unlimited.
     * @param  array  $withRelations  The relations to load with the content.
     * @return ContentCollection<TResult>
     */
    public function getUnderRealPath(string $realPath, $limit = null, $withRelations = []);

    /**
     * Find content by slug under the specified real path.
     *
     * @param string $realPath The real path under which to search for the content.
     * @param string $slug The slug of the content to find.
     * @return null | TResult The content found by the slug under the specified real path.
     */
    public function findBySlugUnderRealPath(string $realPath, string $slug);

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
