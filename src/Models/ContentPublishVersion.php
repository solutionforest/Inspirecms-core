<?php

namespace SolutionForest\InspireCms\Models;

use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\ContentPublishVersion as ContentPublishVersionContract;
use SolutionForest\InspireCms\Support\Base\Models\BasePivotModel;

class ContentPublishVersion extends BasePivotModel implements ContentPublishVersionContract
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function content()
    {
        return $this->belongsTo(InspireCmsConfig::getContentModelClass(), 'content_id');
    }

    public function version()
    {
        return $this->belongsTo(InspireCmsConfig::getContentVersionModelClass(), 'version_id');
    }

    //region Scope(s)
    public function scopeWhereIsPublished($query)
    {
        return $query->where('published_at', '<', now());
    }
    //endregion Scope(s)
}
