<?php

namespace SolutionForest\InspireCms\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Base\BasePolicy;
use SolutionForest\InspireCms\Facades\PermissionManifest;
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
        return static::authorizeModel($user, __FUNCTION__);
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @param  Content|Model  $content
     * @return bool
     */
    public function view($user, Content $content)
    {
        return static::authorizeModel($user, __FUNCTION__, $content?->getKey());
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @param  Content|Model  $content
     * @return bool
     */
    public function update($user, Content $content)
    {
        return static::authorizeModel($user, __FUNCTION__, $content?->getKey());
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @param  Content|Model  $content
     * @return bool
     */
    public function delete($user, Content $content)
    {
        return static::authorizeModel($user, __FUNCTION__, $content?->getKey());
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @return bool
     */
    public function deleteAny($user)
    {
        return static::authorizeModel($user, __FUNCTION__);
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @param  Content|Model  $content
     * @return bool
     */
    public function forceDelete($user, Content $content)
    {
        return static::authorizeModel($user, __FUNCTION__, $content?->getKey());
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @return bool
     */
    public function forceDeleteAny($user)
    {
        return static::authorizeModel($user, __FUNCTION__);
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @param  Content|Model  $content
     * @return bool
     */
    public function restore($user, Content $content)
    {
        return static::authorizeModel($user, __FUNCTION__, $content?->getKey());
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @return bool
     */
    public function restoreAny($user)
    {
        return static::authorizeModel($user, __FUNCTION__);
    }

    public function reorderChildren($user, $content = null)
    {
        return static::authorizeModel($user, __FUNCTION__, $content?->getKey());
    }

    public function viewHistory($user, $content = null)
    {
        // dd(static::guessTieredPermissionName(__FUNCTION__, $content?->getKey()));
        return static::authorizeModel($user, __FUNCTION__, $content?->getKey());
    }

    public function setAsDefault($user, $content = null)
    {
        return static::authorizeModel($user, __FUNCTION__, $content?->getKey());
    }

    protected static function guessTieredPermissionName($ability, $id)
    {
        if (PermissionManifest::isTieredPermissionGranted(class_basename(Content::class))) {
            return PermissionManifest::getTieredPermissionNameForModel($ability, Content::class, $id);
        }
        return null;
    }

    protected static function authorizeModel($user, $ability, $id = null)
    {
        $ability = Str::snake($ability);
        if (! Str::endsWith($ability, 'Any') && $ability != 'create'
            && $id !== null
            && ($tieredPermission = static::guessTieredPermissionName($ability, $id))
            && $user?->can($tieredPermission)
        ) {
            return true;
        }

        return $user?->can(static::guessPermissionName($ability, Content::class));
    }
}
