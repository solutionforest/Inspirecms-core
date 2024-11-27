<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Concerns\CmsUserTrait;
use SolutionForest\InspireCms\Models\Contracts\User as UserContract;
use SolutionForest\InspireCms\Support\Base\Models\BaseAuthenticatableModel;

class User extends BaseAuthenticatableModel implements UserContract
{
    use CmsUserTrait;
    use HasUuids;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'is_active',
    ];

    protected $casts = [
        'last_lockouted_at' => 'datetime',
        'last_password_change_date' => 'datetime',
        'last_logged_in_at' => 'datetime',
        'email_confirmed_at' => 'datetime',
    ];

    public function userActivity()
    {
        return $this->hasOne(InspireCmsConfig::getUserLoginActivityModelClass(), 'user_id');
    }
}
