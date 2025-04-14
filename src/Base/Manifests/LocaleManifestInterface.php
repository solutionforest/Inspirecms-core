<?php

namespace SolutionForest\InspireCms\Base\Manifests;

interface LocaleManifestInterface
{
    public function addUserPreferredLocale(string $locale): void;

    public function removeUserPreferredLocale(string $locale): void;

    public function getUserPreferredLocales(): array;

    public function getLocales(): array;

    public function getLocaleLabelsFor(array $locales, ?string $displayLocale = null): array;
    
    public function getLocaleLabel(string $locale, ?string $displayLocale = null): string;
}
