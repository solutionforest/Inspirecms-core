<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use SolutionForest\InspireCms\Collection\ContentCollection;
use SolutionForest\InspireCms\Content\SegmentProviderInterface;
use SolutionForest\InspireCms\Factories\ContentSegmentFactory;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Models\Scopes\ContentVersionDetailScope;
use SolutionForest\InspireCms\Models\Scopes\DocumentTypeScope;

/**
 * @implements ContentServiceInterface<\SolutionForest\InspireCms\Models\Content>
 */
class ContentService implements ContentServiceInterface
{
    protected SegmentProviderInterface $segmentProvider;

    public function __construct()
    {
        $this->segmentProvider = ContentSegmentFactory::create();
    }

    /** {@inheritDoc} */
    public function findByIds($ids, $isWebPage = null, $isPublished = null, $withRelations = [], $sorting = [], $limit = 10)
    {
        return $this->buildFindByIdsQuery($ids, $isWebPage, $isPublished, $withRelations, $sorting, $limit)
            ->find($ids);
    }

    public function findByRoutePatternWithLangId($uri, $isDefaultRoutePattern, $isWebPage = null, $withRelations = [], $sorting = [], $limit = 10)
    {
        $query = $this
            ->buildFindWithRouteQuery(fn ($q) => $q
                ->where('uri', $uri)
                ->when($isDefaultRoutePattern != null, fn ($q) => $q->whereIsDefaultPattern())
            )
            ->with($withRelations);

        $query = $this->applyScopeFilters($query, [
            'whereIsWebPage' => $isWebPage,
        ]);
        $query = $this->applySortingAndLimit($query, $sorting, $limit);
        
        $records = $query->get();

        return collect($records)
            ->map(fn ($record) => [
                'content' => $record,
                'language_id' => $record?->__route_language_id,
            ]);
    }

    /** {@inheritDoc} */
    public function findByRealPath($path, $isWebPage = null, $isPublished = null, $withRelations = [], $sorting = [], $limit = 10)
    {
        $query = $this->buildFindByRealPathQuery($path, $isWebPage, $isPublished, $withRelations, $sorting, $limit);
    
        // Key the result by the path
        return $query
            ->get()
            ->keyBy(fn ($content) => $content->path->value);
    }

    /** {@inheritDoc} */
    public function getUnderRealPath($path, $isWebPage = null, $isPublished = null, $withRelations = [], $sorting = [], $limit = 10)
    {
        return $this->buildGetUnderRealPathQuery($path, $isWebPage, $isPublished, $withRelations, $sorting, $limit)
            ->get();
    }

    /** {@inheritDoc} */
    public function getPaginatedByIds($ids, $page = 1, $perPage = 10, $pageName = 'page', $isWebPage = null, $isPublished = null, $withRelations = [], $sorting = [])
    {
        return $this->buildFindByIdsQuery($ids, $isWebPage, $isPublished, $withRelations, $sorting, null)
            ->paginate($perPage, ['*'], $pageName, $page)
            ->tap(fn ($paginator) => $this->initializePaginatorCollection($paginator));
    }

    /** {@inheritDoc} */
    public function getPaginatedByRealPath($path, $page = 1, $perPage = 10, $pageName = 'page', $isWebPage = null, $isPublished = null, $withRelations = [], $sorting = [])
    {
        return $this->buildFindByRealPathQuery($path, $isWebPage, $isPublished, $withRelations, $sorting, null)
            ->paginate($perPage, ['*'], $pageName, $page)
            ->tap(fn ($paginator) => $this->initializePaginatorCollection($paginator));
    }

    /** {@inheritDoc} */
    public function getPaginatedUnderRealPath($path, $page = 1, $perPage = 10, $pageName = 'page', $isWebPage = null, $isPublished = null, $withRelations = [], $sorting = [])
    {
        return $this->buildGetUnderRealPathQuery($path, $isWebPage, $isPublished, $withRelations, $sorting, null)
            ->paginate($perPage, ['*'], $pageName, $page)
            ->tap(fn ($paginator) => $this->initializePaginatorCollection($paginator));
    }

    /** {@inheritDoc} */
    public function getDefaultTemplateFor($content)
    {
        return $content->getDefaultTemplate() ?? $content->documentType?->getDefaultTemplate();
    }

    /** {@inheritDoc} */
    public function getTemplatesFor($content)
    {
        if (is_null($content)) {
            return collect();
        }

        return collect($content->documentType?->getTemplates())->merge($content->getTemplates());
    }

    /** {@inheritDoc} */
    public function getTemplateFor($content, $templateSlug)
    {
        // Get the templates from the document type
        if ($content->documentType?->relationLoaded('templates')) {
            //
        } elseif ($content->relationLoaded('documentType')) {
            $content->documentType->load('templates');
        } else {
            $content->load('documentType.templates');
        }
        $templates = $content->documentType?->getTemplates()?->keyBy('slug') ?? collect();

        // Get the templates from the content
        if ($content->relationLoaded('templates')) {
            //
        } else {
            $content->loadMissing('templates');
        }

        if (($contentTemplates = $content->getTemplates()) && $contentTemplates->isNotEmpty()) {
            $templates = $templates->merge($contentTemplates->keyBy('slug'));
        }

        return $templates->get($templateSlug);
    }

    // region Helpers
    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildBaseQuery()
    {
        return static::getModel()::query()
            ->withGlobalScope(DocumentTypeScope::class, app(DocumentTypeScope::class))
            ->withGlobalScope(ContentVersionDetailScope::class, app(ContentVersionDetailScope::class));
    }

    /**
     * @param \Closure(\Illuminate\Database\Eloquent\Builder)   $routeQueryCallback
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildFindWithRouteQuery(\Closure $routeQueryCallback)
    {
        $model = app($this->getModel())->routes()->getRelated();

        return $this->buildBaseQuery()
            ->joinRelationship(
                relationName: 'routes',
                callback: fn ($q) => $routeQueryCallback($q),
            )
            ->addSelect(DB::raw($model->qualifyColumn('language_id') . ' as __route_language_id'));
    }

    protected function buildUnderPathQuery(string $path)
    {
        return $this->buildBaseQuery()
            ->whereHas(
                'parent',
                fn ($q) => $q
                    ->whereHas(
                        'path',
                        fn ($subQ) => $subQ
                            ->where('value', $path)
                    )
            );
    }

    protected function buildFindByPathQuery(string | array $path)
    {
        $formattedPaths = collect(is_string($path) ? [$path] : $path)
            ->flatten()
            ->map(fn ($p) => trim($p, '/'))
            ->all();

        return $this->buildBaseQuery()
            ->whereHas(
                'path',
                fn ($q) => $q
                    ->whereIn('value', $formattedPaths)
            );
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder   $query
     * @param  array<string, string>  $sorting
     * @param  int|null  $limit
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applySortingAndLimit($query, array $sorting, ?int $limit = null)
    {
        if (count($sorting) > 0) {
            foreach ($sorting as $column => $direction) {

                if (! is_string($column) || ! is_string($direction)) {
                    continue;
                }

                $direction = strtolower(trim($direction));

                if (! in_array($direction, ['asc', 'desc'])) {
                    $direction = 'asc';
                }

                $query->orderBy($column, $direction);
            }
        }

        if ($limit != null) {
            $query->take($limit);
        }

        return $query;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder   $query
     * @param  array<string,mixed>  $where
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyScopeFilters($query, $where)
    {
        if (count($where) > 0) {

            foreach ($where as $method => $args) {

                // Skip it since: $args is null = no filter
                if (is_null($args)) {
                    continue;
                }

                if (is_array($args)) {
                    $query->{$method}(...$args);
                } else {
                    $query->{$method}($args);
                }
            }
        }
        return $query;
    }

    /**
     * @return class-string<Model & Content>
     */
    protected static function getModel()
    {
        return InspireCmsConfig::getContentModelClass();
    }
    // endregion Helpers

    private function buildFindByIdsQuery($ids, $isWebPage = null, $isPublished = null, $withRelations = [], $sorting = [], $limit = null)
    {
        $ids = is_string($ids) ? $ids : Arr::flatten($ids);

        $query = $this->buildBaseQuery()->with($withRelations);
        
        $query = $this->applyScopeFilters($query, [
            'whereIsWebPage' => $isWebPage,
            'whereIsPublished' => $isPublished,
        ]);
        $query = $this->applySortingAndLimit($query, $sorting, $limit);

        return $query;
    }

    private function buildFindByRealPathQuery($path, $isWebPage = null, $isPublished = null, $withRelations = [], $sorting = [], $limit = null)
    {
        $query = $this->buildFindByPathQuery($path)
            ->with($withRelations)
            ->with('path');

        $query = $this->applyScopeFilters($query, [
            'whereIsWebPage' => $isWebPage,
            'whereIsPublished' => $isPublished,
        ]);
        $query = $this->applySortingAndLimit($query, $sorting, $limit);
    
        return $query;
    }


    private function buildGetUnderRealPathQuery($path, $isWebPage = null, $isPublished = null, $withRelations = [], $sorting = [], $limit = null)
    {
        $query = $this->buildUnderPathQuery($path)->with($withRelations);
        
        $query = $this->applyScopeFilters($query, [
            'whereIsWebPage' => $isWebPage,
            'whereIsPublished' => $isPublished,
        ]);
        $query = $this->applySortingAndLimit($query, $sorting, $limit);

        return $query;
    }

    private function initializePaginatorCollection($paginator)
    {
        if ($paginator instanceof \Illuminate\Contracts\Pagination\Paginator) {
            
            $items = $paginator->getCollection();

            // for "toDto" method
            if ($items instanceof ContentCollection) {
                $items = $items->setPaginator($paginator);
            }

            $paginator->setCollection($items);

        }

        return $paginator;
    }
}
