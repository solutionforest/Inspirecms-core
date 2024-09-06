<?php

namespace SolutionForest\InspireCms\Base;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

abstract class BaseMorphPivotModel extends MorphPivot
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('inspirecms.models.table_name_prefix') . $this->getTable());
    }
}
