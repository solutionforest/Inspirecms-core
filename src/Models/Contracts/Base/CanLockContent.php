<?php

namespace SolutionForest\InspireCms\Models\Contracts\Base;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use SolutionForest\InspireCms\Exceptions\UnauthorizedOwnerException;

interface CanLockContent
{
    /**
     * @return HasOne
     */
    public function locked();

    /**
     * Locks the content.
     *
     * @param AuthenticatableContract|Model|null $user The user who is locking the content. If null, the current user will be used.
     * @return void
     */
    public function lock($user = null);

    /**
     * Determine if the given user is the owner for locking the content.
     *
     * @param AuthenticatableContract|Model|null $user The user to check ownership for. If null, the current user will be used.
     * @return bool True if the user is the owner for locking the content, false otherwise.
     */
    public function isOwnerForLock($user = null);

    /**
     * Unlocks the content.
     *
     * @param AuthenticatableContract|Model|null $user The user attempting to unlock the content.
     * @return bool
     * 
     * @throws UnauthorizedOwnerException If the user is not the owner of the lock.
     */
    public function unlock($user = null);

    /**
     * Determine if the content is locked.
     *
     * @return bool 
     */
    public function isLocked(): bool;
}