<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface ContentPublishVersion 
{
    public function content(): BelongsTo;

    public function version(): BelongsTo;
}
