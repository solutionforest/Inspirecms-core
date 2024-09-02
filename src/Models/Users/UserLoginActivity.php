<?php

namespace SolutionForest\InspireCms\Models\Users;

use SolutionForest\InspireCms\Base\BaseModel;
use SolutionForest\InspireCms\Models\Contracts\UserLoginActivity as CmsUserLoginActivityContract;

class UserLoginActivity extends BaseModel implements CmsUserLoginActivityContract
{
    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'last_logged_in_at_utc' => 'datetime',
        'last_logged_out_at_utc' => 'datetime',
    ];
}
