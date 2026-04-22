<?php

namespace SolutionForest\InspireCms\Models\Polymorphic;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use SolutionForest\InspireCms\Models\Contracts\Templateable as TemplateableContract;
use SolutionForest\InspireCms\Support\Base\Models\BaseMorphPivotModel;

class Templateable extends BaseMorphPivotModel implements TemplateableContract
{
    protected $guarded = [];

    public $timestamps = false;

    protected $table = 'templateable';

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function templateable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeIsDefault($query)
    {
        return $query->where('is_default');
    }
}
