<?php

namespace SolutionForest\InspireCms\Content;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Dtos\LanguageDto;
use SolutionForest\InspireCms\Models\Contracts\Content;

interface SegmentProviderInterface
{
    /**
     * Generate the URL segment for the given content.
     *
     * @param  Model & Content  $content
     * @param  null|string|LanguageDto  $locale
     * @return ?string The generated URL segment.
     */
    public function getUrlSegment($content, $locale = null);

    /**
     * @param  Model & Content  $content
     * @return string
     */
    public function getSegment($content);

    /**
     * @param  Model & Content  $content
     * @return array
     */
    public function getSegments($content);

    /**
     * Retrieves a route segment based on the given slug and prefixes.
     *
     * @param  string  $slug  The slug to use for the route segment
     * @param  string|string[]  ...$prefixes  Variable number of prefixes to apply to the route segment
     * @return string
     */
    public function getRouteSegmentWithPrefix($slug, ...$prefixes);

    /**
     * @param  Model & Content  $content
     * @return string
     */
    public function getPath($content);

    /**
     * Get the default route pattern.
     *
     * @return string The default route pattern.
     */
    public function getDefaultRoutePattern();

    /**
     * Get the default route constraints.
     *
     * @return array The default route constraints.
     */
    public function getDefaultRouteConstraints();

    /**
     * Get the URL segment from the default route.
     *
     * @param  \Illuminate\Routing\Route  $route  The route instance.
     * @return string The URL segment derived from the default route.
     */
    public function getUrlSegmentFromDefaultRoute($route);

    /**
     * Retrieve the locale from the default route.
     *
     * @param  \Illuminate\Routing\Route  $route  The route instance.
     * @return string The locale extracted from the route.
     */
    public function getLocaleFromDefaultRoute($route);
}
