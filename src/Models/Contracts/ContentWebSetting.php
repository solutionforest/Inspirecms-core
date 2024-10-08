<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface ContentWebSetting
{
    public function redirectContent(): BelongsTo;
}
