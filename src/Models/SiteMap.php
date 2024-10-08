<?php

namespace SolutionForest\InspireCms\Models;

use SolutionForest\InspireCms\Base\BaseModel;
use SolutionForest\InspireCms\Models\Contracts\SiteMap as SiteMapContract;

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
