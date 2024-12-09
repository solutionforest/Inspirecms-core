<?php

namespace SolutionForest\InspireCms\Base\Manifests;

use SolutionForest\InspireCms\InspireCmsConfig;

class LocaleManifest implements LocaleManifestInterface
{
    protected array $locales = [];

    public function __construct()
    {
        $this->locales = collect(InspireCmsConfig::get('available_locales', []))->mapWithKeys(fn ($locale) => [$locale => $locale])->all();
    }

    public function addLocale(string $locale): void
    {
        $this->locales[$locale] = $locale;
    }

    public function getLocales(): array
    {
        return $this->locales;
    }

    public function selectOptions(?string $displayLocale = null): array
    {
        return collect($this->locales)->mapWithKeys(fn ($locale) => [
            $locale => locale_get_display_name($locale, $displayLocale ?? app()->getLocale()),
        ])->all();
    }
}
