<?php

namespace SolutionForest\InspireCms\Dtos\Concerns;

trait Translatable
{
    /**
     * @var ?string
     */
    protected $locale;

    /**
     * @var ?string
     */
    protected $fallbackLocale;

    /**
     * @param string
     * @return self
     */
    public function setLocale(string $locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string
     * @return self
     */
    public function setFallbackLocale(string $locale)
    {
        $this->fallbackLocale = $locale;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getFallbackLocale()
    {
        return $this->fallbackLocale;
    }

    protected function getTranslations($translations, ?string $locale = null, bool $usingFallback = true)
    {
        if (! $translations || ! is_array($translations)) {
            return $translations;
        }

        $locale = $locale ?? $this->getLocale();
        $fallbackLocale = $this->getFallbackLocale();

        $value = data_get($translations, $locale);

        if (! $value && $usingFallback && $locale !== $fallbackLocale && $fallbackLocale) {
            $value = $this->getTranslations($translations, $fallbackLocale, false);
        }

        return $value;
    }
}
