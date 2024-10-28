<?php

namespace SolutionForest\InspireCms\PathGenerators;

use SolutionForest\InspireCms\Models\Contracts\Content;

interface ContentPathGeneratorInterface
{
    /**
     * Generates the path for the given content.
     *
     * @param  Content  $content  The content for which the path is being generated.
     * @param  string|null  $locale  Optional locale for the path generation. Defaults to null.
     * @return string The generated path for the content.
     */
    public function getPath(Content $content, ?string $locale = null): string;

    /**
     * Generating the full path of a given content.
     *
     * @return string The full path for the content.
     */
    public function getFullPath(Content $content): string;

    /**
     * Retrieve the path pattern.
     *
     * @return string The path pattern as a string.
     */
    public function getPathPattern(): string;

    /**
     * Interface method to retrieve the route name.
     *
     * @return string The name of the route.
     */
    public function getRouteName(): string;

    /**
     * Interface method to retrieve a slug from the given request.
     *
     * @param  \Illuminate\Http\Request  $request  The request object from which the slug is to be extracted.
     * @param  string  $locale  The locale to be used for the slug extraction.
     * @return string|null The slug extracted from the request, or null if not found.
     */
    public function getSlugFromRequest($request, $locale): ?string;
}
