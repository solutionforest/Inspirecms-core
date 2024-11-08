<?php

namespace SolutionForest\InspireCms\Models\Polymorphic;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\FieldGroupable as FieldGroupableContract;
use SolutionForest\InspireCms\Observers\FieldGroupableObserver;
use SolutionForest\InspireCms\Support\Base\Models\BaseMorphPivotModel;
use Spatie\EloquentSortable\SortableTrait;

#[ObservedBy(FieldGroupableObserver::class)]
class FieldGroupable extends BaseMorphPivotModel implements FieldGroupableContract
{
    use SortableTrait;

    protected $guarded = ['id'];

    public $timestamps = false;

    public $table = 'field_groupables';

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    public function fieldGroup(): BelongsTo
    {
        return $this->belongsTo(InspireCmsConfig::getFieldGroupModelClass());
    }

    public function groupabled(): MorphTo
    {
        return $this->morphTo();
    }

    public function inheritedFrom(): MorphTo
    {
        return $this->morphTo();
    }
}
