<?php

namespace SolutionForest\InspireCms\Models\Polymorphic;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use SolutionForest\InspireCms\Base\BaseModel;
use SolutionForest\InspireCms\Models\Concerns\NestableTrait;
use SolutionForest\InspireCms\Models\Contracts\NestableTree as NestableTreeContract;
use Spatie\EloquentSortable\SortableTrait;

class NestableTree extends BaseModel implements NestableTreeContract
{
    use NestableTrait;
    use SortableTrait;

    protected $guarded = ['id'];

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    public function nestable(): MorphTo
    {
        return $this->morphTo();
    }

    public function buildSortQuery(): Builder
    {
        $query = method_exists(parent::class, 'buildSortQuery') ? parent::buildSortQuery() : static::query();

        return $query->where('parent_id', $this->parent_id);
    }
}
