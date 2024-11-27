<?php

namespace SolutionForest\InspireCms\Models\Users;

use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\UserLoginActivity as UserLoginActivityContract;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;

class UserLoginActivity extends BaseModel implements UserLoginActivityContract
{
    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'last_logged_in_at_utc' => 'datetime',
        'last_logged_out_at_utc' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(InspireCmsConfig::getUserLoginActivityModelClass(), 'user_id');
    }
}
