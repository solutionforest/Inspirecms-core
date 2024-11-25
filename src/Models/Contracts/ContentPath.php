<?php

namespace SolutionForest\InspireCms\Models\Contracts;

interface ContentPath
{
    /**
     * Get the content that this path belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function content();
}
