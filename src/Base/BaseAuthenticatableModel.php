<?php

namespace SolutionForest\InspireCms\Base;

use Illuminate\Foundation\Auth\User as Authenticatable;

abstract class BaseAuthenticatableModel extends Authenticatable
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('inspirecms.models.table_name_prefix') . $this->getTable());
    }
}
