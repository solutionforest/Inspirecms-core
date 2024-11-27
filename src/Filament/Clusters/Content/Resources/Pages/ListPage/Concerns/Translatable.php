<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages\ListPage\Concerns;

use Filament\Resources\Concerns\HasActiveLocaleSwitcher;

trait Translatable
{
    use HasActiveLocaleSwitcher;

    public function mountTranslatable(): void
    {
        // Set default locale if not set
        if (empty($this->activeLocale)) {
            $this->activeLocale = static::getResource()::getDefaultTranslatableLocale();
        }
        // $this->activeLocale = static::getResource()::getDefaultTranslatableLocale();
    }

    public function getTranslatableLocales(): array
    {
        return static::getResource()::getTranslatableLocales();
    }

    public function getActiveTableLocale(): ?string
    {
        return $this->activeLocale;
    }
}
