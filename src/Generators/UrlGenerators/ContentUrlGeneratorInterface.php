<?php

namespace SolutionForest\InspireCms\Generators\UrlGenerators;

use SolutionForest\InspireCms\Dtos\LanguageDto;
use SolutionForest\InspireCms\Models\Contracts\Content;

interface ContentUrlGeneratorInterface
{
    /**
     * Generates a URL for the given content.
     *
     * @param  Content  $content  The content for which the URL is being generated.
     * @param  string|null|LanguageDto  $locale  The locale to use for the URL. If null, the default locale will be used.
     * @return ?string The generated URL.
     */
    public function getUrl(Content $content, $locale = null);

    /**
     * Generates a localized URL based on the provided slug path and locale.
     *
     * @param  string  $slugPath  The slug path for the content.
     * @param  string  $locale  The locale for the URL.
     * @return string The localized URL.
     */
    public function getLocalizedUrl($slugPath, $locale);

    /**
     * Interface method to retrieve the route name.
     *
     * @return string The name of the route.
     */
    public function getRouteName(): string;

    /**
     * Retrieve the path pattern.
     *
     * @return string The path pattern as a string.
     */
    public function getPathPattern(): string;

    /**
     * Retrieve the locale from the given request.
     *
     * @param  \Illuminate\Http\Request  $request  The request object from which to extract the locale.
     * @return string|null The locale extracted from the request.
     */
    public function getLocaleFromRequest($request): ?string;

    /**
     * Interface method to retrieve a slug from the given request.
     *
     * @param  \Illuminate\Http\Request  $request  The request object from which the slug is to be extracted.
     * @param  string  $locale  The locale to be used for the slug extraction.
     * @return string|null The slug extracted from the request, or null if not found.
     */
    public function getSlugFromRequest($request, $locale): ?string;
}
