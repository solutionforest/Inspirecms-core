<?php

namespace SolutionForest\InspireCms\Models\Contracts;

interface Language 
{
    /**
     * Find or create the default language.
     *
     * This method attempts to retrieve the default language. If it does not exist,
     * a new default language instance will be created and returned.
     *
     * @return Language The found or newly created default language instance.
     */
    public static function findOrCreateDefaultLanguage(): Language;
}
