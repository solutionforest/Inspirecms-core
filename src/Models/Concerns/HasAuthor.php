<?php

namespace SolutionForest\InspireCms\Models\Concerns;

trait HasAuthor
{
    public static function bootHasAuthor()
    {
        static::creating(function ($model) {
            if (empty($model->author_id) && empty($model->author_type)) {
                $author = $model->resolveAuthor();

                $model->author_id = $author?->getKey();
                $model->author_type = $author?->getMorphClass();

            }
        });
    }

    public function author()
    {
        return $this->morphTo();
    }

    protected function resolveAuthor()
    {
        $resolver = config('inspirecms.resolvers.user', \SolutionForest\InspireCms\Resolver\UserResolver::class);

        return $resolver::resolve();
    }
}
