<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use SolutionForest\InspireCms\Base\BaseModel;
use SolutionForest\InspireCms\Models\Contracts\ContentVersion as ContentVersionContract;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class ContentVersion extends BaseModel implements ContentVersionContract
{
    use Concerns\HasAuthor;

    protected $guarded = ['id'];

    protected $casts = [
        'from_data' => 'array',
        'to_data' => 'array',
    ];

    public $timestamps = false;

    public function content(): BelongsTo
    {
        return $this->belongsTo(InspireCmsConfig::getContentModelClass(), 'content_id');
    }

    public function publishLog(): HasOne
    {
        return $this->hasOne(InspireCmsConfig::getContentPublishVersionModelClass(), 'version_id');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }
}
