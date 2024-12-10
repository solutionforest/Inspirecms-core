<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $templateable_type
 * @property string $templateable_id
 * @property int $template_id
 * @property bool $is_default
 */
interface Templateable
{
    /**
     * Get the templateable entity.
     *
     * @return MorphTo The templateable entity.
     */
    public function templateable(): MorphTo;
}
