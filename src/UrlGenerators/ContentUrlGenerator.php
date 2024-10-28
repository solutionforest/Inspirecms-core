<?php

namespace SolutionForest\InspireCms\UrlGenerators;

use SolutionForest\InspireCms\Dtos\LanguageDto;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Factories\ContentPathGeneratorFactory;
use SolutionForest\InspireCms\Models\Contracts\Content;

class ContentUrlGenerator implements ContentUrlGeneratorInterface
{
    /** {@inheritDoc} */
    public function getUrl(Content $content, ?string $locale = null, bool $useFallbackLocale = false): string
    {
        $pathGenerator = ContentPathGeneratorFactory::create();

        if (blank($locale) && $useFallbackLocale) {
            $locale = InspireCms::getFallbackLanguage()?->locale ?? '';
        }

        $relatedLanguage = collect(InspireCms::getAllAvailableLanguages())->firstWhere(fn (LanguageDto $language) => $language->locale === $locale);
        if (is_null($relatedLanguage)) {
            $locale = '';
        }

        if (! blank($locale)) {
            return $this->getLocalizedUrl($pathGenerator->getFullPath($content), $locale);
        }

        $path = $pathGenerator->getPath($content, $locale);

        return url($path);
    }

    /** {@inheritDoc} */
    public function getLocalizedUrl(string $path, string $locale): string
    {
        $pathGenerator = ContentPathGeneratorFactory::create();

        $routeName = $pathGenerator->getRouteName();

        try {

            return url()->route($routeName, ['locale' => $locale, 'slug' => $path]);

        } catch (\Throwable $th) {

            $currRequest = request();

            $scheme = $currRequest->getScheme();
            $domainName = $currRequest->getHost();
            if ($currRequest->getPort() !== 80) {
                $domainName .= ':' . $currRequest->getPort();
            }

            return str_replace(
                ['{scheme}', '{domain_name}', '{locale}', '{slug?}'],
                [$scheme, $domainName, $locale, $path],
                $this->getUrlPattern(),
            );
        }
    }

    /** {@inheritDoc} */
    public function getUrlPattern(): string
    {
        return '{scheme}://{domain_name}/{locale}/{slug?}';
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
            ->where(fn (LanguageDto $language) => $language->locale === $locale)
            ->first();

        if (is_null($language)) {
            return null;
        }

        return $locale;
    }

    protected function getLocaleFromPath(string $path): ?string
    {
        $parts = explode('/', $path);

        return $parts[0] ?? null;
    }
}
