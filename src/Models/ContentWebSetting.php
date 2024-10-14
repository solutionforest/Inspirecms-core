<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SolutionForest\InspireCms\Models\Contracts\ContentWebSetting as ContentWebSettingContract;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class ContentWebSetting extends BaseModel implements ContentWebSettingContract
{
    protected $guarded = ['id'];

    protected $casts = [
        'seo' => 'json',
        'robots' => 'json',
    ];

    public function redirectContent(): BelongsTo
    {
        return $this->belongsTo(InspireCmsConfig::getContentModelClass(), 'redirect_content_id');
    }
}
