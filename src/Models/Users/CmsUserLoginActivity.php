<?php

namespace SolutionForest\InspireCms\Models\Users;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class CmsUserLoginActivity extends Model
{
    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'last_logged_in_at_utc' => 'datetime',
        'last_logged_out_at_utc' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(InspireCmsConfig::getUserLoginActivityTableName());
    }
}
