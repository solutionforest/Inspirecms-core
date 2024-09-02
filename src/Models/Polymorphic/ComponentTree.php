<?php

namespace SolutionForest\InspireCms\Models\Polymorphic;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use SolutionForest\InspireCms\Base\BaseModel;
use SolutionForest\InspireCms\Models\Concerns\NestableTrait;
use SolutionForest\InspireCms\Models\Contracts\ComponentTree as CmsComponentTreeContract;
use Spatie\EloquentSortable\SortableTrait;

/**
 * This class represents a component in the CMS system. It uses the BelongToCmsComponentTree trait
 * to manage its hierarchical order and structure through a relationship with CmsComponentTree.
 */
class ComponentTree extends BaseModel implements CmsComponentTreeContract
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
