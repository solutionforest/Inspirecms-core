<?php

namespace SolutionForest\InspireCms\Models\Polymorphic;

use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\FieldGroupable as FieldGroupableContract;
use SolutionForest\InspireCms\Observers\FieldGroupableObserver;
use SolutionForest\InspireCms\Support\Base\Models\BaseMorphPivotModel;
use Spatie\EloquentSortable\SortableTrait;

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

    public function fieldGroup()
    {
        return $this->belongsTo(InspireCmsConfig::getFieldGroupModelClass());
    }

    public function groupabled()
    {
        return $this->morphTo();
    }

    public function inheritedFrom()
    {
        return $this->morphTo();
    }

    public static function booted()
    {
        parent::booted();

        static::observe(FieldGroupableObserver::class);
    }
}
