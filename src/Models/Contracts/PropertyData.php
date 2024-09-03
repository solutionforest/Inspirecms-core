<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface PropertyData
{
    /**
     * Get the content versions associated with the property data.
     *
     * This method should return a HasMany relationship
     * representing the content versions linked to the property data.
     *
     * @return HasMany The associated content versions.
     */
    public function contentVersion(): HasMany;

    /**
     * Determine if the property data is published.
     *
     * This method checks if the property data has been published.
     *
     * @return bool True if published, false otherwise.
     */
    public function isPublished(): bool;
}
