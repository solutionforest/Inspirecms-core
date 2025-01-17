<?php

namespace SolutionForest\InspireCms\Models;

use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\ContentPath as ContentPathContract;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;

class ContentPath extends BaseModel implements ContentPathContract
{
    protected $guarded = ['id'];

    public $timestamps = false;

    public function content()
    {
        return $this->belongsTo(InspireCmsConfig::getContentModelClass(), 'key');
    }
}
