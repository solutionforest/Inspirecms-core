<?php

namespace SolutionForest\InspireCms\Services;

use Closure;
use Laravel\Scout\Searchable;
use SolutionForest\InspireCms\Base\Services\BaseModelSerivce;

abstract class IndexSearchService extends BaseModelSerivce implements IndexSearchServiceInterface
{
    public function __construct(string $modelClass)
    {
        parent::__construct($modelClass);
    }

    public function searchOne(string $keyword, ?Closure $searchBuilder = null, ?Closure $queryBuilder = null)
    {
        $this->guardAgainstNonSearchableModel();

        $builder = $this->model::search($keyword);

        if ($searchBuilder) {
            $builder = $searchBuilder($builder);
        }

        if ($queryBuilder) {
            $builder->query(fn ($query) => $queryBuilder($query));
        }

        return $builder->first();
    }

    public function search(string $keyword, ?Closure $searchBuilder = null, ?Closure $queryBuilder = null)
    {
        $this->guardAgainstNonSearchableModel();

        $builder = $this->model::search($keyword);

        if ($searchBuilder) {
            $builder = $searchBuilder($builder);
        }

        if ($queryBuilder) {
            $builder->query(fn ($query) => $queryBuilder($query));
        }

        return $builder->get();
    }

    protected function modelIsSearchable(): bool
    {
        return in_array(Searchable::class, class_uses_recursive($this->model));
    }

    protected function guardAgainstNonSearchableModel()
    {
        if (! $this->modelIsSearchable()) {
            throw new \Exception('Model is not searchable');
        }
    }
}
