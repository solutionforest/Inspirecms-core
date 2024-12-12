<?php

namespace SolutionForest\InspireCms\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Base\BasePolicy;
use SolutionForest\InspireCms\Models\Contracts\Content;

class ContentPublishPolicy extends BasePolicy
{
    /**
     * @param  Authenticatable|User|Model  $user
     * @param  null|Content|Model  $content
     * @return bool
     */
    public function publish($user, $content = null)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, Content::class));
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @param  null|Content|Model  $content
     * @return bool
     */
    public function unpublish($user, $content)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, Content::class));
    }
}
