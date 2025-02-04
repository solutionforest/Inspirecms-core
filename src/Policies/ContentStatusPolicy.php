<?php

namespace SolutionForest\InspireCms\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\Content;

class ContentStatusPolicy extends ContentPolicy
{
    /**
     * @param  Authenticatable|User|Model  $user
     * @param  null|Content|Model  $content
     * @return bool
     */
    public function publish($user, $content = null)
    {
        return static::authorizeModel($user, __FUNCTION__, $content?->getKey());
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @param  null|Content|Model  $content
     * @return bool
     */
    public function unpublish($user, $content)
    {
        return static::authorizeModel($user, __FUNCTION__, $content?->getKey());
    }
}
