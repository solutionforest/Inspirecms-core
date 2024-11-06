<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasDtoModel;

interface ContentWebSetting extends HasDtoModel
{
    public function redirectContent(): BelongsTo;
}
