<?php

namespace SolutionForest\InspireCms\Models\Contracts;

interface Language
{
    /**
     * Get the language code.
     *
     * @return string The language code.
     */
    public function getCode(): string;

    /**
     * Get the route pattern for the language.
     *
     * @return string The route pattern for the language.
     */
    public function routePattern(): string;

    /**
     * Get the language label.
     *
     * @return string The language label.
     */
    public function getLabel(): string;

    /**
     * Determine if the language is the default language.
     *
     * @return bool True if the language is default, false otherwise.
     */
    public function isDefault(): bool;

    /**
     * Find or create the default language.
     *
     * @return Language The found or newly created default language instance.
     */
    public static function findOrCreateDefaultLanguage(): Language;
}
