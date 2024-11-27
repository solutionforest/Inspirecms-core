<?php

namespace SolutionForest\InspireCms\Models\Contracts;

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
     * @return string The language label.
     */
    public function getLabel();

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
