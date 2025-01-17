<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Content\SegmentProviderInterface;
use SolutionForest\InspireCms\Factories\ContentSegmentFactory;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;

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
    public function findPublishedWebPageById($id)
    {
        return $this->buildWebPageQuery()
            ->whereIsPublished()
            ->find($id);
    }

    /** {@inheritDoc} */
    public function findPublishedContentById($id)
    {
        return $this->buildBaseQuery()
            ->whereIsPublished()
            ->find($id);
    }

    /** {@inheritDoc} */
    public function findPublishedContentByIds(...$ids)
    {
        $ids = Arr::flatten($ids);

        return $this->buildBaseQuery()
            ->whereIsPublished()
            ->findMany($ids);
    }

    /** {@inheritDoc} */
    public function findWebPageAndLangIdByDefaultRoute($urlSegment)
    {
        $content = $this->buildFindWebPageByRouteQuery(fn ($q) => $q
            ->where('uri', $urlSegment)
            ->whereIsDefaultPattern()
        )->first();

        return [$content, $content?->__route_language_id];
    }

    /** {@inheritDoc} */
    public function findWebPageAndLangIdByRoutePattern($routePattern)
    {
        $content = $this->buildFindWebPageByRouteQuery(fn ($q) => $q
            ->where('uri', $routePattern)
        )->first();

        return [$content, $content?->__route_language_id];
    }

    /** {@inheritDoc} */
    public function findByRealPath(string $path)
    {
        return $this->buildFindByPathQuery(trim($path, '/'))
            ->first();
    }

    /** {@inheritDoc} */
    public function getByRealPath($paths, $withRelations = [])
    {
        $paths = collect($paths)
            ->flatten()
            ->map(fn ($path) => trim($path, '/'))
            ->all();

        $result = $this->buildFindByPathQuery($paths)
            ->with($withRelations)
            ->with('path')
            ->get();
        // Key the result by the path
        return $result->keyBy(fn ($content) => $content->path->value);
    }

    /** {@inheritDoc} */
    public function getUnderRealPath(string $path, $limit = null, $withRelations = [])
    {
        return $this->buildUnderPathQuery($path)
            ->with($withRelations)
            ->when(! is_null($limit), fn ($q) => $q->limit($limit))
            ->get();
    }

    /** {@inheritDoc} */
    public function findBySlugUnderRealPath(string $path, string $slug)
    {
        return $this->buildUnderPathQuery($path)
            ->where('slug', $slug)
            ->first();
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
        return static::getModel()::query();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildWebPageQuery()
    {
        return $this->buildBaseQuery()
            ->whereIsWebPage();
    }

    protected function buildFindWebPageByRouteQuery(\Closure $routeQueryCallback)
    {
        $model = app($this->getModel())->routes()->getRelated();
        return $this->buildWebPageQuery()
            ->joinRelationship(
                relationName: 'routes',
                callback: fn ($q) => $routeQueryCallback($q)
            )
            ->addSelect($model->qualifyColumn('language_id as __route_language_id'));
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

    protected function buildFindByPathQuery(string|array $path)
    {
        if (is_array($path)) {
            return $this->buildBaseQuery()
                ->whereHas(
                    'path', 
                    fn ($q) => $q
                        ->whereIn('value', $path)
                );
        }
        return $this->buildBaseQuery()
            ->whereHas(
                'path', 
                fn ($q) => $q
                    ->where('value', $path)
            );
    }

    /**
     * @return class-string<Model & Content>
     */
    protected static function getModel()
    {
        return InspireCmsConfig::getContentModelClass();
    }
    // endregion Helpers
}
