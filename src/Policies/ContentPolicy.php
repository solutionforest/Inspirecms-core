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
     * @param Authenticatable|User|Model $user
     * @return bool
     */
    public function create($user)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, Content::class));
    }

    /**
     * @param Authenticatable|User|Model $user
     * @param Content|Model $content
     * @return bool
     */
    public function update($user, Content $content)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, Content::class));
    }

    /**
     * @param Authenticatable|User|Model $user
     * @param Content|Model $content
     * @return bool
     */
    public function delete($user, Content $content)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, Content::class));
    }

    /**
     * @param Authenticatable|User|Model $user
     * @param null|Content|Model $content
     * @return bool
     */
    public function publish($user, $content)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, Content::class));
    }

    /**
     * @param Authenticatable|User|Model $user
     * @param null|Content|Model $content
     * @return bool
     */
    public function unpublish($user, $content)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, Content::class));
    }

    /**
     * @param Authenticatable|User|Model $user
     * @param null|Content|Model $content
     * @return bool
     */
    public function setPrivate($user, $content)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, Content::class));
    }
}
