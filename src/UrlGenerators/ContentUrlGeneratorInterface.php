<?php

namespace SolutionForest\InspireCms\UrlGenerators;

use SolutionForest\InspireCms\Models\Contracts\Content;

interface ContentUrlGeneratorInterface
{
    public function getUrl(Content $content, ?string $locale = null, bool $useFallbackLocale = false): string;

    public function getLocalizedUrl(string $path, string $locale): string;

    /**
     * Retrieve the locale from the given request.
     *
     * @param  \Illuminate\Http\Request  $request  The request object from which to extract the locale.
     * @return string|null The locale extracted from the request.
     */
    public function getLocaleFromRequest($request): ?string;
}
