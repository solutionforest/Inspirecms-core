<?php

namespace SolutionForest\InspireCmsApi\Facades;

use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCmsApi\Services\ApiRouteGenerator;

/**
 * @method static \Illuminate\Support\Collection getApiEnabledDocumentTypes()
 * @method static \Illuminate\Support\Collection getEndpoints()
 * @method static mixed findDocumentTypeBySlug(string $slug)
 *
 * @see \SolutionForest\InspireCmsApi\Services\ApiRouteGenerator
 */
class InspireCmsApi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ApiRouteGenerator::class;
    }
}
