<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use SolutionForest\InspireCms\Base\BaseAuthenticatableModel;
use SolutionForest\InspireCms\Models\Concerns\CmsUserTrait;
use SolutionForest\InspireCms\Models\Contracts\User as UserContract;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class User extends BaseAuthenticatableModel implements UserContract
{
    use CmsUserTrait;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'last_lockouted_at' => 'datetime',
        'last_password_change_date' => 'datetime',
        'last_logged_in_at' => 'datetime',
        'email_confirmed_at' => 'datetime',
    ];

    public function userActivity(): HasOne
    {
        return $this->hasOne(InspireCmsConfig::getUserLoginActivityModelClass(), 'user_id');
    }
}
