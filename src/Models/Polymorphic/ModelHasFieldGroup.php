<?php

namespace SolutionForest\InspireCms\Models\Polymorphic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SolutionForest\FilamentFieldGroup\Models\FieldGroup;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class ModelHasFieldGroup extends Model implements Sortable
{
    use SortableTrait;

    protected $guarded = ['id'];

    public $incrementing = false;

    public $timestamps = false;

    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('inspirecms-core.models.model_has_field_groups.table_name'));
    }

    public function fieldGroup(): BelongsTo
    {
        return $this->belongsTo(config('filament-field-group.models.field_group', FieldGroup::class));
    }
}
