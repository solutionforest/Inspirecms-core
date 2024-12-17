<?php

namespace SolutionForest\InspireCms\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Base\BasePolicy;
use SolutionForest\InspireCms\Models\Contracts\User;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

class MediaAssetPolicy extends BasePolicy
{
    /**
     * @param  Authenticatable|User|Model  $user
     * @return bool
     */
    public function create($user)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, MediaAsset::class));
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @param  MediaAsset&Model  $asset
     * @return bool
     */
    public function update($user, $asset)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, MediaAsset::class));
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @param  MediaAsset&Model  $asset
     * @return bool
     */
    public function view($user, $asset)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, MediaAsset::class));
    }

    /**
     * @param  Authenticatable|User|Model  $user
     * @param  MediaAsset&Model  $asset
     * @return bool
     */
    public function delete($user, $asset)
    {
        return $user?->can(static::guessPermissionName(__FUNCTION__, MediaAsset::class));
    }
}
