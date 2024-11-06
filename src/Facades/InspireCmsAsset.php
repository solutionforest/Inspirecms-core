<?php

namespace SolutionForest\InspireCms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SolutionForest\InspireCms\Base\Assets\InspireCmsAssetManagerInterface
 */
class InspireCmsAsset extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return \SolutionForest\InspireCms\Base\Assets\InspireCmsAssetManagerInterface::class;
    }
}
