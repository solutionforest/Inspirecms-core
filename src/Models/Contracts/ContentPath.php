<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $content_id
 * @property string $slug_path
 * @property string $encoded_path
 * @property-read null | Model & Content $content
 */
interface ContentPath
{
    /**
     * Get the content that this path belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function content();
}
