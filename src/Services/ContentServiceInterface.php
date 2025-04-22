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
     * Find content item(s) by its ID.
     *
     * @param  string|string[]  $id  The ID of the content item to find.
     * @param  bool|null  $isWebPage  Filter by web page status (true/false/null for all)
     * @param  bool|null  $isPublished  Filter by published status (true/false/null for all)
     * @param array $withRelations Relations to eager load
     * @param  array<string,string> $sorting Sorting options (e.g. [field => direction])
     * @param  int|null  $limit  The maximum number of content items to retrieve, or null for unlimited.
     * @return ContentCollection<TResult>
     */
    public function findByIds($ids, $isWebPage = null, $isPublished = null, $withRelations = [], $sorting = [], $limit = 10);

    /**
     * Find content item(s) by their route pattern and language ID.
     *
     * @param  string  $uri  The URI of the content item to find.
     * @param  bool  $isDefaultRoutePattern  Whether to filter by default route pattern.
     * @param  bool|null  $isWebPage  Whether to filter by web page status.
     * @param  bool|null  $isPublished  Filter by published status (true/false/null for all)
     * @param array $withRelations Relations to eager load
     * @param  array<string,string> $sorting Sorting options (e.g. [field => direction])
     * @param  int|null  $limit  The maximum number of content items to retrieve, or null for unlimited.
     * @return Collection<array{content:TResult,language_id:int}>
     */
    public function findByRoutePatternWithLangId($uri, $isDefaultRoutePattern, $isWebPage = null, $isPublished = null, $withRelations = [], $sorting = [], $limit = 10);

    /**
     * Find content item(s) by their real path.
     *
     * @param  string|string[]  $path  The real path of the content item to find. (e.g. '/home/blogs/2023/10/01')
     * @param  bool|null  $isWebPage  Filter by web page status (true/false/null for all)
     * @param  bool|null  $isPublished  Filter by published status (true/false/null for all)
     * @param array $withRelations Relations to eager load
     * @param  array<string,string> $sorting Sorting options (e.g. [field => direction])
     * @param  int|null  $limit  The maximum number of content items to retrieve, or null for unlimited.
     * @return Collection<string,TResult> Keyed by the path.
     */
    public function findByRealPath($path, $isWebPage = null, $isPublished = null, $withRelations = [], $sorting = [], $limit = 10);

    /**
     * Get content items under the given real path.
     *
     * @param  string|string[]  $path  The real path under which to find content items.
     * @param  bool|null  $isWebPage  Filter by web page status (true/false/null for all)
     * @param  bool|null  $isPublished  Filter by published status (true/false/null for all)
     * @param array $withRelations Relations to eager load
     * @param  array<string,string> $sorting Sorting options (e.g. [field => direction])
     * @param  int|null  $limit  The maximum number of content items to retrieve, or null for unlimited.
     * @return ContentCollection<TResult>
     */
    public function getUnderRealPath($path, $isWebPage = null, $isPublished = null, $withRelations = [], $sorting = [], $limit = 10);

    /**
     * Retrieves content items by document type.
     *
     * @param string $documentType The document type to filter by
     * @param  bool|null  $isWebPage  Filter by web page status (true/false/null for all)
     * @param  bool|null  $isPublished  Filter by published status (true/false/null for all)
     * @param array $withRelations Relations to eager load
     * @param array<string,string> $sorting Sorting options (e.g. [field => direction])
     * @param  int|null  $limit  The maximum number of content items to retrieve, or null for unlimited.
     * 
     * @return ContentCollection<TResult>
     */
    public function getByDocumentType($documentType, $isWebPage = null, $isPublished = null, $withRelations = [], $sorting = [], $limit = 10);

    /**
     * Get content items by their IDs with pagination.
     *
     * @param  string|string[]  $ids  The IDs of the content items to retrieve.
     * @param  int  $page  The page number to retrieve.
     * @param  int|null  $perPage  The number of items per page.
     * @param  string  $pageName  The name of the page parameter in the query string.
     * @param  bool|null  $isWebPage  Filter by web page status (true/false/null for all)
     * @param  bool|null  $isPublished  Filter by published status (true/false/null for all)
     * @param array $withRelations Relations to eager load
     * @param  array<string,string> $sorting Sorting options (e.g. [field => direction])
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<TResult>
     */
    public function getPaginatedByIds($ids, $page = 1, $perPage = 10, $pageName = 'page', $isWebPage = null, $isPublished = null, $withRelations = [], $sorting = []);

    /**
     * Get content items by their real path with pagination.
     *
     * @param  string  $path  The real path to filter content items by
     * @param  int  $page  The current page number
     * @param  int|null  $perPage  The number of items per page.
     * @param  string  $pageName  Name of the page query parameter
     * @param  bool|null  $isWebPage  Filter by web page status (true/false/null for all)
     * @param  bool|null  $isPublished  Filter by published status (true/false/null for all)
     * @param array $withRelations Relations to eager load
     * @param  array<string,string> $sorting Sorting options (e.g. [field => direction])
     * @return \Illuminate\Pagination\LengthAwarePaginator<TResult>
     */
    public function getPaginatedByRealPath($path, $page = 1, $perPage = 10, $pageName = 'page', $isWebPage = null, $isPublished = null, $withRelations = [], $sorting = []);

    /**
     * Get content items under the given real path with pagination.
     *
     * @param  string|string[]  $path  The real path under which to find content items.
     * @param  int  $page  The current page number
     * @param  int|null  $perPage  The number of items per page.
     * @param  string  $pageName  Name of the page query parameter
     * @param  bool|null  $isWebPage  Filter by web page status (true/false/null for all)
     * @param  bool|null  $isPublished  Filter by published status (true/false/null for all)
     * @param array $withRelations Relations to eager load
     * @param  array<string,string> $sorting Sorting options (e.g. [field => direction])
     * @param  int|null  $limit  The maximum number of content items to retrieve, or null for unlimited.
     * @return \Illuminate\Pagination\LengthAwarePaginator<TResult>
     */
    public function getPaginatedUnderRealPath($path, $page = 1, $perPage = 10, $pageName = 'page', $isWebPage = null, $isPublished = null, $withRelations = [], $sorting = []);

    /**
     * Get content items by document type with pagination.
     *
     * @param string $documentType The document type to filter by
     * @param  int  $page  The current page number
     * @param  int|null  $perPage  The number of items per page.
     * @param  string  $pageName  Name of the page query parameter
     * @param  bool|null  $isWebPage  Filter by web page status (true/false/null for all)
     * @param  bool|null  $isPublished  Filter by published status (true/false/null for all)
     * @param array $withRelations Relations to eager load
     * @param  array<string,string> $sorting Sorting options (e.g. [field => direction])
     * @return \Illuminate\Pagination\LengthAwarePaginator<TResult>
     */
    public function getPaginatedByDocumentType($documentType, $page = 1, $perPage = 10, $pageName = 'page', $isWebPage = null, $isPublished = null, $withRelations = [], $sorting = []);

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
