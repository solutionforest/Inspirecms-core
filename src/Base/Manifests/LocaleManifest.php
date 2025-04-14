<?php

namespace SolutionForest\InspireCms\Base\Manifests;

use SolutionForest\InspireCms\InspireCmsConfig;

class LocaleManifest implements LocaleManifestInterface
{
    protected array $locales = [];

    protected array $userPreferredLocales = [];

    public function __construct()
    {
        $this->locales = collect(InspireCmsConfig::get('localization.available_locales', ['en', 'fr', 'zh_CN', 'zh_TW', 'es', 'ja', 'de']))
            ->mapWithKeys(fn ($locale) => [$locale => $locale])
            ->all();

        $this->userPreferredLocales = collect(InspireCmsConfig::get('localization.user_preferred_locales', ['en', 'zh_CN', 'zh_TW']))
            ->mapWithKeys(fn ($locale) => [$locale => $locale])
            ->all();
    }

    public function addUserPreferredLocale(string $locale): void
    {
        $this->userPreferredLocales[$locale] = $locale;
    }

    public function removeUserPreferredLocale(string $locale): void
    {
        unset($this->userPreferredLocales[$locale]);
    }

    public function getUserPreferredLocales(): array
    {
        return $this->userPreferredLocales;
    }

    public function getLocales(): array
    {
        return $this->locales;
    }

    public function getLocaleLabelsFor(array $locales, ?string $displayLocale = null): array
    {
        return collect($locales)
            ->mapWithKeys(fn ($locale) => [
                $locale => $this->getLocaleLabel($locale, $displayLocale),
            ])
            ->all();
    }

    public function getLocaleLabel(string $locale, ?string $displayLocale = null): string
    {
        return locale_get_display_name($locale, $displayLocale ?? app()->getLocale());
    }
}
