<?php

namespace SolutionForest\InspireCms\Models;

use SolutionForest\InspireCms\Models\Contracts\SiteMap as SiteMapContract;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;

class SiteMap extends BaseModel implements SiteMapContract
{
    protected $guarded = ['id'];

    protected $casts = [
        'enable' => 'boolean',
    ];

    public function model()
    {
        return $this->morphTo();
    }
}
