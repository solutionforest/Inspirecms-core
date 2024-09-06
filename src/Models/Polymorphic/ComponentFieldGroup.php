<?php

namespace SolutionForest\InspireCms\Models\Polymorphic;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use SolutionForest\InspireCms\Base\BaseModel;
use SolutionForest\InspireCms\Models\Contracts\ComponentFieldGroup as ComponentFieldGroupContract;
use SolutionForest\InspireCms\Support\InspireCmsConfig;
use Spatie\EloquentSortable\SortableTrait;

class ComponentFieldGroup extends BaseModel implements ComponentFieldGroupContract
{
    use SortableTrait;

    protected $guarded = ['id'];

    public $timestamps = false;

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    public function fieldGroup(): BelongsTo
    {
        return $this->belongsTo(InspireCmsConfig::getFieldGroupModelClass());
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
