<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface ContentVersion 
{
    /**
     * Get the content associated with the content version.
     *
     * This method should return a BelongsTo relationship
     * representing the content linked to this version.
     *
     * @return BelongsTo The associated content.
     */
    public function content(): BelongsTo;

    /**
     * Get the property data associated with the content version.
     *
     * This method should return a BelongsTo relationship
     * representing the property data linked to this content version.
     *
     * @return BelongsTo The associated property data.
     */
    public function propertyData(): BelongsTo;
}
