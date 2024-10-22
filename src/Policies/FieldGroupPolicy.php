<?php

namespace SolutionForest\InspireCms\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentFieldGroup\Models\Contracts\FieldGroup;
use SolutionForest\InspireCms\Base\BasePolicy;
use SolutionForest\InspireCms\Models\Contracts\User;

class FieldGroupPolicy extends BasePolicy
{
    /**
     * @param  Authenticatable|User|Model  $user
     * @return bool
     */
    public function create($user)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, FieldGroup::class));
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @return bool
     */
    public function viewAny($user)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, FieldGroup::class));
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @param  FieldGroup|Model  $fieldGroup
     * @return bool
     */
    public function update($user, FieldGroup $fieldGroup)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, FieldGroup::class));
    }

    /**
     * @param  Authenticatable|User|Model  $fieldGroup
     * @param  FieldGroup|Model  $fieldGroup
     * @return bool
     */
    public function delete($user, FieldGroup $fieldGroup)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, FieldGroup::class));
    }

    /**
     * @param  Authenticatable|User|Model  $fieldGroup
     * @return bool
     */
    public function attach($user)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, FieldGroup::class));
    }

    /**
     * @param  Authenticatable|User|Model  $fieldGroup
     * @param  FieldGroup|Model  $fieldGroup
     * @return bool
     */
    public function detach($user, FieldGroup $fieldGroup)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, FieldGroup::class));
    }

    /**
     * @param  Authenticatable|User|Model  $fieldGroup
     * @return bool
     */
    public function reorder($user)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, FieldGroup::class));
    }

    /**
     * @param  Authenticatable|User|Model  $fieldGroup
     * @return bool
     */
    public function replicate($user)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, FieldGroup::class));
    }
}
