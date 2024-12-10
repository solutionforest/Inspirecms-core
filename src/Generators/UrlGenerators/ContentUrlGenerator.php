<?php

namespace SolutionForest\InspireCms\Generators\UrlGenerators;

use SolutionForest\InspireCms\Dtos\LanguageDto;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Models\Contracts\Content;

class ContentUrlGenerator implements ContentUrlGeneratorInterface
{
    /** {@inheritDoc} */
    public function getUrl(Content $content, $locale = null)
    {
        $contentPath = $content->path;
        if (! $contentPath) {
            return null;
        }

        $locale = ($locale instanceof LanguageDto ? $locale : collect(InspireCms::getAllAvailableLanguages())->get($locale)) ?? InspireCms::getFallbackLanguage();

        $localeCode = $locale?->isDefault ? null : $locale?->code;

        return $this->getLocalizedUrl($contentPath->slug_path ?? '', $localeCode);
    }

    public function getLocalizedUrl($slugPath, $locale)
    {
        $routeName = $this->getRouteName();

        try {

            return url()->route($routeName, ['locale' => $locale, 'slug' => ltrim($slugPath, '/')]);

        } catch (\Throwable $th) {

            if (! blank($locale)) {
                $fullPath = str_replace(
                    ['{locale?}', '{slug?}'],
                    [
                        $locale,
                        ltrim($slugPath, '/'),
                    ],
                    $this->getPathPattern()
                );
            } else {
                $fullPath = $slugPath;
            }

            return url($fullPath);
        }
    }

    /** {@inheritDoc} */
    public function getPathPattern(): string
    {
        return '{locale?}/{slug?}';
    }

    /** {@inheritDoc} */
    public function getRouteName(): string
    {
        return 'inspirecms.content.show';
    }

    /** {@inheritDoc} */
    public function getLocaleFromRequest($request): ?string
    {
        $path = $request->path();
        $locale = $this->getLocaleFromPath($path);

        if (blank($locale)) {
            return null;
        }

        $language = collect(InspireCms::getAllAvailableLanguages())
            ->where(fn (LanguageDto $language) => $language->code === $locale)
            ->first();

        if (is_null($language)) {
            return null;
        }

        return $locale;
    }

    /** {@inheritDoc} */
    public function getSlugFromRequest($request, $locale): ?string
    {
        $path = $request->path();
        $parts = explode('/', $path);
        $segmentCount = count($parts);

        // Only one segment, it's the slug
        if ($segmentCount <= 1) {
            $slug = $parts[0] ?? null;

            // If the locale is the same as the slug, return null
            if ($locale == $slug) {
                return null;
            }

            return $slug;
        }

        // More than one segment, the last segment is the slug without locale
        $filteredParts = [];
        foreach ($parts as $i => $path) {

            if ($i === 0 && $path === $locale) {
                continue;
            }

            $filteredParts[] = $path;
        }

        return implode('/', $filteredParts);
    }

    protected function getLocaleFromPath(string $path): ?string
    {
        $parts = explode('/', $path);

        return $parts[0] ?? null;
    }
}
