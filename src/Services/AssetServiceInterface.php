<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Support\Collection;

/**
 * @template TResult of \Illuminate\Database\Eloquent\Model|\SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset
 */
interface AssetServiceInterface
{
    /**
     * @param  string|string[]  $keys
     * @return Collection<TResult>
     */
    public function findByKeys($keys);
}
