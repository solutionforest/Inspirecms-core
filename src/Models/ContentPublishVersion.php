<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SolutionForest\InspireCms\Base\BasePivotModel;
use SolutionForest\InspireCms\Models\Contracts\ContentPublishVersion as ContentPublishVersionContract;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class ContentPublishVersion extends BasePivotModel implements ContentPublishVersionContract
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function content(): BelongsTo
    {
        return $this->belongsTo(InspireCmsConfig::getContentModelClass(), 'content_id');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(InspireCmsConfig::getContentVersionModelClass(), 'version_id');
    }
}
