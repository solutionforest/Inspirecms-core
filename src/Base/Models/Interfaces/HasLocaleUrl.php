<?php

namespace SolutionForest\InspireCms\Base\Models\Interfaces;

use SolutionForest\InspireCms\Dtos\LanguageDto;

interface HasLocaleUrl
{
    /**
     * Get the URL for the given locale.
     *
     * @param  null|string|LanguageDto  $locale  The locale for which to get the URL. If null, the default locale will be used.
     * @return string|null The URL for the given locale, or null if no URL is available.
     */
    public function getUrl(null | string | LanguageDto $locale = null);
}
