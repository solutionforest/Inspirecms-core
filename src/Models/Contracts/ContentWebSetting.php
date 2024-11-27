<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasDtoModel;

interface ContentWebSetting extends HasDtoModel
{
    /**
     * Define a relationship to redirect content.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function redirectContent();
}
