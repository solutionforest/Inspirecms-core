<?php

namespace SolutionForest\InspireCms\Models\Contracts\Base;

use Illuminate\Database\Eloquent\Relations\HasOne;

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
}
