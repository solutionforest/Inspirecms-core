<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface ContentVersion
{
    /**
     * Get the content associated with the content version.
     *
     * @return BelongsTo The associated content.
     */
    public function content(): BelongsTo;

    /**
     * Get the property data associated with the content version.
     *
     * @return BelongsTo The associated property data.
     */
    public function propertyData(): BelongsTo;
}
