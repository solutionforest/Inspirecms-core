<?php

namespace SolutionForest\InspireCms\Base\Assets;

use Illuminate\Database\Eloquent\Model;

interface InspireCmsAssetManagerInterface
{

    /**
     * @param string|int $key
     * @return ?Model
     */
    public function findByKey(string|int $key);

    /**
     * @param string|int ...$keys
     * @return ?Model
     */
    public function findByKeys(...$keys);

    public function getAssetUrl(Model|string|int $asset): string;

    public function getAssetMiddleware(): array;
}
