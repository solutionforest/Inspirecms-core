<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $key The ID of the content.
 * @property string $value
 * @property-read null | Model & Content $content
 */
interface ContentPath
{
    /**
     * @return BelongsTo
     */
    public function content();
}
