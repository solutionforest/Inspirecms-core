<?php

namespace SolutionForest\InspireCms\Models\Contracts\Base;

use Illuminate\Database\Eloquent\Relations\HasOne;
use SolutionForest\InspireCms\Dtos\LanguageDto;

interface HasContentWebSetting
{
    /**
     * Define a one-to-one relationship with the WebSetting model.
     */
    public function webSetting(): HasOne;

    /**
     * Determine if indexing is allowed.
     *
     * @return bool True if indexing is allowed, false otherwise.
     */
    public function isAllowIndex(): bool;

    /**
     * Determine if following is allowed.
     *
     * @return bool True if following is allowed, false otherwise.
     */
    public function isAllowFollow(): bool;

    /**
     * Determine if the content is redirectable.
     *
     * @return bool True if the content can be redirected, false otherwise.
     */
    public function isRedirectable(): bool;

    /**
     * Get the redirect URL for the given locale.
     *
     * @param LanguageDto|string|null $locale
     * @return string|null The redirect URL for the specified locale, or null if no redirect URL is set.
     */
    public function getRedirectUrl($locale = null);

    /**
     * Get the type of redirect.
     *
     * @return int The redirect type.
     */
    public function getRedirectType(): int;
}
