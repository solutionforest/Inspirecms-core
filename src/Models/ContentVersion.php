<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SolutionForest\InspireCms\Base\BaseModel;
use SolutionForest\InspireCms\Models\Contracts\ContentVersion as CmsContentVersionContract;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class ContentVersion extends BaseModel implements CmsContentVersionContract
{
    protected $guarded = ['id'];

    public $timestamps = false;

    public function content(): BelongsTo
    {
        return $this->belongsTo(InspireCmsConfig::getContentModelClass(), 'content_id');
    }

    public function propertyData(): BelongsTo
    {
        return $this->belongsTo(InspireCmsConfig::getPropertyDataModelClass(), 'property_data_id');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }
}
