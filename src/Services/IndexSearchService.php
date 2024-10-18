<?php

namespace SolutionForest\InspireCms\Services;

use Laravel\Scout\Searchable;
use SolutionForest\InspireCms\Base\Services\BaseModelSerivce;

abstract class IndexSearchService extends BaseModelSerivce implements IndexSearchServiceInterface
{
    public function __construct(string $modelClass)
    {
        parent::__construct($modelClass);
    }

    public function searchOne(string $keyword)
    {
        $this->guardAgainstNonSearchableModel();

        return $this->model::search($keyword)->first();
    }

    public function search(string $keyword)
    {
        $this->guardAgainstNonSearchableModel();

        return $this->model::search($keyword)->get();
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
