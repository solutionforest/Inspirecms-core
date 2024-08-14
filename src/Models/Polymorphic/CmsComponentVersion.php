<?php

namespace SolutionForest\InspireCms\Models\Polymorphic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CmsComponentVersion extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_current' => 'boolean',
        'is_published' => 'boolean',
        'version_date' => 'datetime',
    ];

    const CREATED_AT = 'version_date';

    const UPDATED_AT = 'version_date';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('inspirecms-core.models.component_version.table_name'));
    }

    public function component(): MorphTo
    {
        return $this->morphTo();
    }
}
