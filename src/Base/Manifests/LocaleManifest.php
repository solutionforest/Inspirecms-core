<?php

namespace SolutionForest\InspireCms\Base\Manifests;

use SolutionForest\InspireCms\InspireCmsConfig;

class LocaleManifest implements LocaleManifestInterface
{
    protected array $userPreferredLocales = [];

    public function __construct()
    {
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

    public function getUserPreferredLocaleLabels(?string $displayLocale = null): array
    {
        return collect($this->getUserPreferredLocales())
            ->mapWithKeys(fn ($locale) => [
                $locale => locale_get_display_name($locale, $displayLocale ?? app()->getLocale()),
            ])
            ->all();
    }
}
