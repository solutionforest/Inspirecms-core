<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasDtoModel;

/**
 * @property int $id
 * @property string $content_id
 * @property array $seo
 * @property array $robots
 * @property ?string $redirect_path
 * @property ?string $redirect_content_id
 * @property ?int $redirect_type
 * @property ?\Carbon\Carbon $created_at
 * @property ?\Carbon\Carbon $updated_at
 * @property null | Model & Content $redirectContent
 */
interface ContentWebSetting extends HasDtoModel
{
    /**
     * Define a relationship to redirect content.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function redirectContent();
}
