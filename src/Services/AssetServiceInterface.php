<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

/**
 * @template TResult of Model|MediaAsset
 */
interface AssetServiceInterface
{
    /**
     * @param  string|string[]  $keys
     * @return Collection<TResult>
     */
    public function findByKeys($keys);
}
