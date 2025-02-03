<?php

namespace SolutionForest\InspireCms\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Base\BasePolicy;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Models\Contracts\User;

class ContentPolicy extends BasePolicy
{
    /**
     * @param  Authenticatable|User|Model  $user
     * @return bool
     */
    public function create($user)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, Content::class));
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @return bool
     */
    public function viewAny($user)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, Content::class));
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @param  Content|Model  $content
     * @return bool
     */
    public function view($user, Content $content)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, Content::class));
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @param  Content|Model  $content
     * @return bool
     */
    public function update($user, Content $content)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, Content::class));
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @param  Content|Model  $content
     * @return bool
     */
    public function delete($user, Content $content)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, Content::class));
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @return bool
     */
    public function deleteAny($user)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, Content::class));
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @param  Content|Model  $content
     * @return bool
     */
    public function forceDelete($user, Content $content)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, Content::class));
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @return bool
     */
    public function forceDeleteAny($user)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, Content::class));
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @param  Content|Model  $content
     * @return bool
     */
    public function restore($user, Content $content)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, Content::class));
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @return bool
     */
    public function restoreAny($user)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, Content::class));
    }

    public function reorderChildren($user, $content = null)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, Content::class));
    }

    public function viewHistory($user, $content = null)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, Content::class));
    }

    public function setAsDefault($user, $content = null)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, Content::class));
    }
}
