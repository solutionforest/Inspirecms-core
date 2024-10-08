<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SolutionForest\InspireCms\Base\BaseModel;
use SolutionForest\InspireCms\Support\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\ContentWebSetting as ContentWebSettingContract;

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
