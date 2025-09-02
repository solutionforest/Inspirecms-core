<?php

namespace SolutionForest\InspireCms\Base\Filament\Resources\Pages\ListContentRecords\Concerns;

use LaraZeus\SpatieTranslatable\Resources\Concerns\HasActiveLocaleSwitcher;

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
