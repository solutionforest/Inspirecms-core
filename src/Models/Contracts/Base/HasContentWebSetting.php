<?php

namespace SolutionForest\InspireCms\Models\Contracts\Base;

use Illuminate\Database\Eloquent\Relations\HasOne;

interface HasContentWebSetting
{
    /**
     * Define a one-to-one relationship with the WebSetting model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
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
     * @param string|null $locale The locale for which to get the redirect URL. If null, the default locale will be used.
     * @return string|null The redirect URL for the specified locale, or null if no redirect URL is set.
     */
    public function getRedirectUrl(?string $locale = null): ?string;

    /**
     * Get the type of redirect.
     *
     * @return int The redirect type.
     */
    public function getRedirectType(): int;
}
