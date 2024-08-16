<?php

namespace SolutionForest\InspireCms\Models\Polymorphic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class CmsComponentVersion extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_current' => 'boolean',
        'properties' => 'json',
        'version_date' => 'datetime',
    ];

    const CREATED_AT = 'version_date';

    const UPDATED_AT = 'version_date';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(InspireCmsConfig::getComponentVersionTableName());
    }

    public function component(): MorphTo
    {
        return $this->morphTo();
    }
}
