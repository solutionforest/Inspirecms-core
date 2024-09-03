<?php

namespace SolutionForest\InspireCms\Facades;

use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCms\Base\Manifests\ContentStatusManifestInterface;

/**
 * @see \SolutionForest\InspireCms\Base\Manifests\ContentStatusManifest
 */
class ContentStatusManifest extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return ContentStatusManifestInterface::class;
    }
}
