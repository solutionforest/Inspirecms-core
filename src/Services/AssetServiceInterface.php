<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Support\Collection;

/**
 * @template TResult of \Illuminate\Database\Eloquent\Model|\SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset
 */
interface AssetServiceInterface
{
    /**
     * @return ?TResult
     */
    public function findByKey(string | int $key);

    /**
     * @param  string|int  ...$keys
     * @return Collection<TResult>
     */
    public function findByKeys(...$keys);
}
