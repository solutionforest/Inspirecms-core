<?php

namespace SolutionForest\InspireCms\Models\Polymorphic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use SolutionForest\InspireCms\Support\InspireCmsConfig;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class CmsComponentFieldGroup extends Model implements Sortable
{
    use SortableTrait;

    protected $guarded = ['id'];

    public $timestamps = false;

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(InspireCmsConfig::getComponentFieldGroupTableName());
    }

    public function fieldGroup(): BelongsTo
    {
        return $this->belongsTo(InspireCmsConfig::getFieldGroupModelClass());
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
