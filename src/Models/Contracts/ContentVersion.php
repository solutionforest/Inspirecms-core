<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

interface ContentVersion
{
    /**
     * Get the content associated with the content version.
     *
     * @return BelongsTo The associated content.
     */
    public function content(): BelongsTo;

    /**
     * Get the associated publish log for the content version.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function publishLog(): HasOne;
}
