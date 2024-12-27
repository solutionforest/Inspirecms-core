<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Collection\ContentCollection;
use SolutionForest\InspireCms\InspireCmsConfig;

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
    public function findByRealPath(string $slugPath, $withRelations = [])
    {
        return $this->getByRealPath($slugPath, $withRelations)->get(trim($slugPath, '/'));
    }

    /** {@inheritDoc} */
    public function getByRealPath(string $slugPath, $withRelations = [])
    {
        $content = $this->getFindByRealPathQuery($slugPath)->with($withRelations)->get();

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
    public function getUnderRealPath(string $slugPath, $limit = null, $withRelations = [])
    {
        $parent = $this->findByRealPath($slugPath);
        if (is_null($parent)) {
            return new ContentCollection;
        }

        return $parent->children()
            ->with($withRelations)
            ->when(! is_null($limit), fn ($q) => $q->limit($limit))
            ->get();
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
        return $this->getTemplatesFor($content)->first(fn ($template) => $template->slug === $templateSlug);
    }

    //region Helpers
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

        return $this->getQuery()->with('ancestorsAndSelf')->where('slug', $trueSlug);
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
    //endregion Helpers
}
