<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use SolutionForest\InspireCms\Facades\InspireCms;

trait HasTranslations
{
    use \Spatie\Translatable\HasTranslations;

    public function getFallbackLocale(): string
    {
        return InspireCms::getFallbackLanguage()?->code ?? 'en';
    }
}
