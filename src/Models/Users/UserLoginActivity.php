<?php

namespace SolutionForest\InspireCms\Models\Users;

use Illuminate\Database\Eloquent\Relations\HasMany;
use SolutionForest\InspireCms\Base\BaseModel;
use SolutionForest\InspireCms\Models\Contracts\UserLoginActivity as CmsUserLoginActivityContract;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class UserLoginActivity extends BaseModel implements CmsUserLoginActivityContract
{
    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'last_logged_in_at_utc' => 'datetime',
        'last_logged_out_at_utc' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(InspireCmsConfig::getUserLoginActivityModelClass(), 'user_id');
    }
}
