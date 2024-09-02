<?php

namespace SolutionForest\InspireCms\Models;

use Filament\Models\Contracts\FilamentUser;
use SolutionForest\InspireCms\Base\BaseAuthenticatableModel;
use SolutionForest\InspireCms\Models\Concerns\CmsUserTrait;
use SolutionForest\InspireCms\Models\Contracts\User as CmsUserContract;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class User extends BaseAuthenticatableModel implements FilamentUser, CmsUserContract
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

    public function userActivity()
    {
        return $this->hasOne(InspireCmsConfig::getUserLoginActivityModelClass(), 'user_id');
    }
}
