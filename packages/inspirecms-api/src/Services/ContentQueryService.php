<?php

namespace SolutionForest\InspireCmsApi\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use SolutionForest\InspireCms\InspireCmsConfig;

class ContentQueryService
{
    public function __construct(
        protected ApiSettingsService $apiSettingsService
    ) {}

    /**
     * Build a query for content based on request parameters.
     */
    public function buildQuery(Model $documentType, Request $request): Builder
    {
        $contentClass = InspireCmsConfig::getContentModelClass();

        $query = $contentClass::query()
            ->where('document_type_id', $documentType->getKey())
            ->with($this->getEagerLoadRelations($documentType, $request));

        // Apply filters
        $this->applyFilters($query, $request);

        // Apply search
        $this->applySearch($query, $request);

        // Apply sorting
        $this->applySorting($query, $request);

        // Apply published status filter (default: only published)
        $this->applyPublishedFilter($query, $request);

        // Apply locale filter
        $this->applyLocaleFilter($query, $request);

        return $query;
    }

    /**
     * Get paginated results.
     */
    public function paginate(Builder $query, Request $request, Model $documentType): LengthAwarePaginator
    {
        $apiSettings = $this->apiSettingsService->getSettings($documentType);
        $maxPerPage = $apiSettings['max_per_page'] ?? config('inspirecms-api.defaults.max_per_page', 100);
        $defaultPerPage = config('inspirecms-api.defaults.per_page', 15);

        $perPage = min(
            (int) $request->input('per_page', $defaultPerPage),
            $maxPerPage
        );

        return $query->paginate($perPage);
    }

    /**
     * Find a single content item by ID.
     */
    public function findById(Model $documentType, string $id, Request $request)
    {
        $contentClass = InspireCmsConfig::getContentModelClass();

        return $contentClass::query()
            ->where('document_type_id', $documentType->getKey())
            ->where('id', $id)
            ->with($this->getEagerLoadRelations($documentType, $request))
            ->first();
    }

    /**
     * Find a single content item by slug.
     */
    public function findBySlug(Model $documentType, string $slug, Request $request)
    {
        $contentClass = InspireCmsConfig::getContentModelClass();

        return $contentClass::query()
            ->where('document_type_id', $documentType->getKey())
            ->where('slug', $slug)
            ->with($this->getEagerLoadRelations($documentType, $request))
            ->first();
    }

    /**
     * Get the relationships to eager load.
     */
    protected function getEagerLoadRelations(Model $documentType, Request $request): array
    {
        $apiSettings = $this->apiSettingsService->getSettings($documentType);
        $defaultIncludes = $apiSettings['default_includes'] ?? [];

        // Parse includes from request
        $requestIncludes = $request->input('include');
        if ($requestIncludes) {
            $requestIncludes = is_array($requestIncludes)
                ? $requestIncludes
                : explode(',', $requestIncludes);
        } else {
            $requestIncludes = [];
        }

        // Merge and filter valid relations
        $includes = array_unique(array_merge($defaultIncludes, $requestIncludes));

        // Define allowed relations (security measure)
        $allowedRelations = [
            'documentType',
            'parent',
            'children',
            'webSetting',
            'author',
            'latestVersion',
            'templates',
        ];

        return array_intersect($includes, $allowedRelations);
    }

    /**
     * Apply filters from request.
     */
    protected function applyFilters(Builder $query, Request $request): void
    {
        $filters = $request->input('filter', []);

        if (! is_array($filters)) {
            return;
        }

        foreach ($filters as $field => $value) {
            // Sanitize field name
            $field = preg_replace('/[^a-zA-Z0-9_]/', '', $field);

            if (is_array($value)) {
                // Handle operators: filter[created_at][gte]=2024-01-01
                foreach ($value as $operator => $operatorValue) {
                    $this->applyFilterOperator($query, $field, $operator, $operatorValue);
                }
            } else {
                // Simple equality: filter[status]=published
                $query->where($field, $value);
            }
        }
    }

    /**
     * Apply a filter with an operator.
     */
    protected function applyFilterOperator(Builder $query, string $field, string $operator, $value): void
    {
        $operatorMap = [
            'eq' => '=',
            'neq' => '!=',
            'gt' => '>',
            'gte' => '>=',
            'lt' => '<',
            'lte' => '<=',
            'like' => 'like',
            'in' => 'in',
            'not_in' => 'not_in',
            'null' => 'null',
            'not_null' => 'not_null',
        ];

        $sqlOperator = $operatorMap[$operator] ?? null;

        if (! $sqlOperator) {
            return;
        }

        switch ($sqlOperator) {
            case 'like':
                $query->where($field, 'like', "%{$value}%");

                break;
            case 'in':
                $values = is_array($value) ? $value : explode(',', $value);
                $query->whereIn($field, $values);

                break;
            case 'not_in':
                $values = is_array($value) ? $value : explode(',', $value);
                $query->whereNotIn($field, $values);

                break;
            case 'null':
                $query->whereNull($field);

                break;
            case 'not_null':
                $query->whereNotNull($field);

                break;
            default:
                $query->where($field, $sqlOperator, $value);
        }
    }

    /**
     * Apply search filter.
     */
    protected function applySearch(Builder $query, Request $request): void
    {
        $search = $request->input('search');

        if (! $search) {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");
        });
    }

    /**
     * Apply sorting.
     */
    protected function applySorting(Builder $query, Request $request): void
    {
        $sort = $request->input('sort', '-created_at');

        if (! $sort) {
            return;
        }

        $sortFields = is_array($sort) ? $sort : explode(',', $sort);

        foreach ($sortFields as $sortField) {
            $sortField = trim($sortField);

            if (empty($sortField)) {
                continue;
            }

            // Check for descending order (prefix with -)
            $direction = 'asc';
            if (str_starts_with($sortField, '-')) {
                $direction = 'desc';
                $sortField = substr($sortField, 1);
            }

            // Sanitize field name
            $sortField = preg_replace('/[^a-zA-Z0-9_]/', '', $sortField);

            // Only allow sorting on specific fields
            $allowedSortFields = ['id', 'title', 'slug', 'created_at', 'updated_at', 'status'];

            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $direction);
            }
        }
    }

    /**
     * Apply published status filter.
     */
    protected function applyPublishedFilter(Builder $query, Request $request): void
    {
        // By default, only show published content
        // Can be overridden with ?status=all for authenticated requests
        $status = $request->input('status', 'published');

        if ($status === 'published') {
            $query->whereIsPublished(true);
        } elseif ($status === 'draft') {
            $query->whereIsPublished(false);
        }
        // 'all' shows both published and draft
    }

    /**
     * Apply locale filter.
     */
    protected function applyLocaleFilter(Builder $query, Request $request): void
    {
        $locale = $request->input('locale');

        if ($locale) {
            // Store locale on request for transformation later
            $request->attributes->set('api_locale', $locale);
        }
    }
}
