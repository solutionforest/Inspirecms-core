<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Support\Str;
use SolutionForest\InspireCms\InspireCmsConfig;

/**
 * @implements ContentServiceInterface<\SolutionForest\InspireCms\Models\Content>
 */
class ContentService implements ContentServiceInterface
{
    protected string $contentModel;

    public function __construct()
    {
        $this->contentModel = InspireCmsConfig::getContentModelClass();
    }

    /** {@inheritDoc} */
    public function findPublishedWebPageById($id)
    {
        return $this->getContentQuery()
            ->whereIsWebPage()
            ->whereIsPublished()
            ->find($id);
    }

    /** {@inheritDoc} */
    public function findDefaultWebPage()
    {
        return $this->getContentQuery()
            ->where('is_default', true)
            ->whereIsWebPage()
            ->first();
    }

    /** {@inheritDoc} */
    public function findWebPageBySlugPath(string $slugPath)
    {
        return $this->getContentQuery()
            ->whereHas('path', fn ($q) => $q->where('slug_path', $slugPath))
            ->whereIsWebPage()
            ->first();
    }

    /** {@inheritDoc} */
    public function getBySlugPath(string $slugPath)
    {
        $trueSlug = Str::afterLast($slugPath, '/');

        $content = $this->getContentQuery()->with('ancestorsAndSelf')->where('slug', $trueSlug)->get();

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
    protected function getContentQuery()
    {
        return $this->contentModel::query();
    }

    /**
     * Apply the provided callbacks to the search builder.
     *
     * @param  \Laravel\Scout\Builder  $builder  The search builder instance.
     * @param  callable|null  $builderCallback  The callback to modify the builder.
     * @param  callable|null  $queryCallback  The callback to modify the query.
     * @return \Laravel\Scout\Builder
     */
    protected function applyCallbacksForSearchBuilder($builder, $builderCallback, $queryCallback)
    {
        if ($builderCallback) {
            $builder = $builderCallback($builder);
        }

        if ($queryCallback) {
            $builder = $builder->query($queryCallback);
        }

        return $builder;
    }
    //endregion Helpers
}
