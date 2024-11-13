<?php

namespace SolutionForest\InspireCms\Generators\PathGenerators;

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
        $content->loadMissing('ancestorsAndSelf');

        $ancestorsAndSelf = collect($content->ancestorsAndSelf)->reverse()->values();

        // dd($ancendantsAndSelf);

        $slugs = [];

        foreach ($ancestorsAndSelf as $index => $item) {

            $itemOrder = $item->nestable_tree_order;

            if (is_null($itemOrder)) {
                $item->loadMissing('nestableTree');
                $itemOrder = $item->nestableTree?->order ?? 0;
            }

            // Skip the root item if it is the first item
            // e.g. format: "/" instead of "/home"
            if ($index == 0 && $itemOrder == 1) {
                continue;
            }

            $slugs[] = $item->slug;
        }

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
