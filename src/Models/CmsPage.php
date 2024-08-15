<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    use Concerns\HasComponentVersions;

    protected $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('inspirecms.models.page.table_name'));
    }
}
