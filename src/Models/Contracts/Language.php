<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property bool $is_default
 * @property ?CarbonInterface $created_at
 * @property ?CarbonInterface $updated_at
 * @property-read Collection<Model & ContentRoute> $contentRoutes
 */
interface Language
{
    /**
     * @return HasMany
     */
    public function contentRoutes();

    /**
     * Get the language code.
     *
     * @return string The language code.
     */
    public function getCode();

    /**
     * Get the language label.
     *
     * @param  string|null  $displayLocale
     * @return string The language label.
     */
    public function getLabel($displayLocale = null);

    /**
     * Determine if the language is the default language.
     *
     * @return bool True if the language is default, false otherwise.
     */
    public function isDefault();
}
