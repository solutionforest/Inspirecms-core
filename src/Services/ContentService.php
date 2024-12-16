<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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
    public function findPublishedContentByIds(...$ids)
    {
        return $this->getQuery()
            ->whereIsPublished()
            ->findMany(Arr::collapse($ids));
    }

    /** {@inheritDoc} */
    public function findContentByIds(...$ids)
    {
        return $this->getQuery()
            ->findMany(Arr::collapse($ids));
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
            ->whereHas('path', fn ($q) => $q->where('slug_path', $slugPath))
            ->whereIsWebPage()
            ->first();
    }

    /** {@inheritDoc} */
    public function getBySlugPath(string $slugPath)
    {
        $trueSlug = Str::afterLast($slugPath, '/');

        $content = $this->getQuery()->with('ancestorsAndSelf')->where('slug', $trueSlug)->get();

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

    //region Helpers
    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getQuery()
    {
        return static::getModel()::query();
    }

    /**
     * @return class-string<Model>
     */
    protected static function getModel()
    {
        return InspireCmsConfig::getContentModelClass();
    }
    //endregion Helpers
}
