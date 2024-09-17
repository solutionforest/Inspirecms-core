<?php

namespace SolutionForest\InspireCms\Models\Polymorphic;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use SolutionForest\InspireCms\Base\BaseMorphPivotModel;
use SolutionForest\InspireCms\Models\Contracts\Templateable as TemplateableContract;

class Templateable extends BaseMorphPivotModel implements TemplateableContract
{
    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function templateable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeIsDefault(Builder $query): void
    {
        $query->where('is_default');
    }
}
