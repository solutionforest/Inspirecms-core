<?php

namespace SolutionForest\InspireCms\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Foundation\Auth\User as Authenticatable;
use SolutionForest\InspireCms\Models\Concerns\CmsUserTrait;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class CmsUser extends Authenticatable implements FilamentUser
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

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(InspireCmsConfig::getUserTableName());
    }

    public function userActivity()
    {
        return $this->hasOne(InspireCmsConfig::getUserLoginActivityModelClass(), 'user_id');
    }
}
