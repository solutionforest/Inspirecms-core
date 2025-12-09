<?php

namespace SolutionForest\InspireCms\Models;

use Filament\Panel;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Concerns\CmsUserTrait;
use SolutionForest\InspireCms\Models\Contracts\User as UserContract;
use SolutionForest\InspireCms\Support\Base\Models\BaseAuthenticatableModel;

class User extends BaseAuthenticatableModel implements UserContract
{
    use CmsUserTrait;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'is_locked',
        'locked_until',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'locked_until' => 'datetime',
        'last_lockouted_at' => 'datetime',
        'last_password_change_date' => 'datetime',
        'last_logged_in_at' => 'datetime',
        'email_confirmed_at' => 'datetime',
    ];

    public function userActivities()
    {
        return $this->hasMany(InspireCmsConfig::getUserLoginActivityModelClass(), 'user_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid7();
            }
        });
    }
}
