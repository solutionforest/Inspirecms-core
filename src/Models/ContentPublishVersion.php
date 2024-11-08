<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SolutionForest\InspireCms\Database\Factories\ContentPublishVersionFactory;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\ContentPublishVersion as ContentPublishVersionContract;
use SolutionForest\InspireCms\Support\Base\Models\BasePivotModel;

class ContentPublishVersion extends BasePivotModel implements ContentPublishVersionContract
{
    use HasFactory;

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

    //region Scope(s)
    public function scopeWhereIsPublished($query)
    {
        return $query->where('published_at', '<', now());
    }
    //endregion Scope(s)

    //region Factory
    protected static function newFactory()
    {
        return ContentPublishVersionFactory::new();
    }
    //endregion Factory
}
