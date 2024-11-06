<?php

namespace SolutionForest\InspireCms\PathGenerators;

use SolutionForest\InspireCms\Models\Contracts\Content;

class ContentPathGenerator implements ContentPathGeneratorInterface
{
    /** {@inheritDoc} */
    public function getPath(Content $content, ?string $locale = null): string
    {
        $path = $this->getFullPath($content);

        $locale ??= '';

        return str_replace(['{locale}', '{slug?}'], [$locale, $path], $this->getPathPattern());
    }

    public function getFullPath(Content $content): string
    {
        $ancestors = $content->ancestors();
        $slugs = [];
        foreach ($ancestors as $ancestor) {
            $slugs[] = $ancestor->slug;
        }
        $slugs[] = $content->slug;

        return implode('/', $slugs);
    }

    /** {@inheritDoc} */
    public function getPathPattern(): string
    {
        return '{locale}/{slug?}';
    }

    /** {@inheritDoc} */
    public function getRouteName(): string
    {
        return 'inspirecms.content.show';
    }

    /** {@inheritDoc} */
    public function getSlugFromRequest($request, $locale): ?string
    {
        $path = $request->path();
        $parts = explode('/', $path);

        if (count($parts) <= 1) {
            $slug = $parts[0] ?? null;

            if ($locale == $slug) {
                return null;
            }

            return $slug;
        }

        return implode('/', array_slice($parts, 1));
    }
}
