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

    public function getLocales(): array
    {
        return array_keys($this->getLocaleLabels());
    }

    public function getLocaleLabels(): array
    {
        return [
            'en_US' => 'English (United States)',
            'en_GB' => 'English (United Kingdom)',
            'es_ES' => 'Spanish (Spain)',
            'fr_FR' => 'French (France)',
            'de_DE' => 'German (Germany)',
            'it_IT' => 'Italian (Italy)',
            'pt_BR' => 'Portuguese (Brazil)',
            'ja_JP' => 'Japanese (Japan)',
            'zh_CN' => 'Chinese (Simplified)',
            'zh_TW' => 'Chinese (Traditional)',
            'ru_RU' => 'Russian (Russia)',
            'ar_SA' => 'Arabic (Saudi Arabia)',
            'hi_IN' => 'Hindi (India)',
            'nl_NL' => 'Dutch (Netherlands)',
            'ko_KR' => 'Korean (South Korea)',
            'tr_TR' => 'Turkish (Turkey)',
            'pl_PL' => 'Polish (Poland)',
            'sv_SE' => 'Swedish (Sweden)'
        ];
    }
}
