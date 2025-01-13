<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Collection\ContentCollection;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Scopes\ContentPathScope;

/**
 * @implements ContentServiceInterface<\SolutionForest\InspireCms\Models\Content>
 */
class ContentService implements ContentServiceInterface
{
    /** {@inheritDoc} */
    public function findPublishedWebPageById($id)
    {
        return $this->getQuery()
            ->whereIsWebPage()
            ->whereIsPublished()
            ->find($id);
    }

    /** {@inheritDoc} */
    public function findPublishedContentById($id)
    {
        return $this->getQuery()
            ->whereIsPublished()
            ->find($id);
    }

    /** {@inheritDoc} */
    public function findPublishedContentByIds(...$ids)
    {
        $ids = Arr::flatten($ids);

        return $this->getQuery()
            ->whereIsPublished()
            ->findMany($ids);
    }

    /** {@inheritDoc} */
    public function findDefaultWebPage()
    {
        return $this->getQuery()
            ->where('is_default', true)
            ->whereIsWebPage()
            ->first();
    }

    /** {@inheritDoc} */
    public function findWebPageBySlugPath(string $slugPath)
    {
        return $this->getQuery()
            ->whereHas('path', fn ($q) => $q->where('slug_path', static::ensureSlugPath($slugPath)))
            ->whereIsWebPage()
            ->first();
    }

    /** {@inheritDoc} */
    public function findByRealPath(string $realPath, $withRelations = [])
    {
        return $this->getByRealPath($realPath, $withRelations)->get(trim($realPath, '/'));
    }

    /** {@inheritDoc} */
    public function getByRealPath(string $realPath, $withRelations = [])
    {
        $content = $this->getFindByRealPathQuery($realPath)->with($withRelations)->get();

        // Find similar content by slug path
        return collect($content)
            ->map(function ($item) {

                // Get the root content
                $root = collect($item->ancestorsAndSelf)->sortBy('depth')->first();
                $slugPathForContent = $root?->reverse_slug_path;

                return [
                    'item' => $item,
                    'slugPath' => $slugPathForContent,
                ];
            })
            ->filter(fn ($arr) => isset($arr['slugPath']) && filled($arr['slugPath']))
            ->pluck('item', 'slugPath');
    }

    /** {@inheritDoc} */
    public function getUnderRealPath(string $realPath, $limit = null, $withRelations = [])
    {
        $parent = $this->findByRealPath($realPath);
        if (is_null($parent)) {
            return new ContentCollection;
        }

        return $parent->children()
            ->with($withRelations)
            ->when(! is_null($limit), fn ($q) => $q->limit($limit))
            ->get();
    }

    /** {@inheritDoc} */
    public function findBySlugUnderRealPath(string $realPath, string $slug)
    {
        $parent = $this->findByRealPath($realPath);
        if (is_null($parent)) {
            return null;
        }

        return $parent->children()
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
    protected function getQuery()
    {
        return static::getModel()::query();
    }

    protected function getFindByRealPathQuery(string $slugPath)
    {
        // Find a content by read slug
        $trueSlug = Str::afterLast($slugPath, '/');

        return $this->getQuery()
            ->with('ancestorsAndSelf', fn ($q) => $q->withoutGlobalScope(ContentPathScope::class))
            ->withoutGlobalScope(ContentPathScope::class)
            ->where('slug', $trueSlug);
    }

    /**
     * @return class-string<Model>
     */
    protected static function getModel()
    {
        return InspireCmsConfig::getContentModelClass();
    }

    protected static function ensureSlugPath($slugPath)
    {
        return Str::of($slugPath)->trim('/')->prepend('/');
    }
    // endregion Helpers
}
