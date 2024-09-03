<?php

namespace SolutionForest\InspireCms\Models\Contracts;

interface Lanauage 
{
    /**
     * Find or create the default language.
     *
     * This method attempts to retrieve the default language. If it does not exist,
     * a new default language instance will be created and returned.
     *
     * @return Lanauage The found or newly created default language instance.
     */
    public static function findOrCreateDefaultLanguage(): Lanauage;
}
