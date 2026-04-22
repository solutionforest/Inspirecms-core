<?php

namespace SolutionForest\InspireCms\Models;

use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\ContentRoute as ContentRouteContract;
use SolutionForest\InspireCms\Observers\ContentRouteObserver;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;

class ContentRoute extends BaseModel implements ContentRouteContract
{
    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'is_default_pattern' => 'boolean',
        'regex_constraints' => 'array',
    ];

    public function content()
    {
        return $this->belongsTo(InspireCmsConfig::getContentModelClass(), 'content_id');
    }

    public function language()
    {
        return $this->belongsTo(InspireCmsConfig::getLanguageModelClass(), 'language_id');
    }

    public function scopeWhereIsDefaultPattern($query, $condition = true)
    {
        return $query->where('is_default_pattern', $condition);
    }

    public static function booted()
    {
        parent::booted();

        static::observe(ContentRouteObserver::class);
    }
}
