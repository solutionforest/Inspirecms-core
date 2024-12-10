<?php

namespace SolutionForest\InspireCms\Models\Contracts;

/**
 * @property int $id
 * @property string $code
 * @property bool $is_default
 * @property ?\Carbon\Carbon $created_at
 * @property ?\Carbon\Carbon $updated_at
 */
interface Language
{
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

    /**
     * Find or create the default language.
     *
     * @return Language&Model The found or newly created default language instance.
     */
    public static function findOrCreateDefaultLanguage();
}
