<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;

interface ContentLock
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function owner();

    /**
     * Determine if the given user is the owner.
     *
     * @param  AuthenticatableContract|Model|null  $user  The user to check ownership for. If null, the current user will be used.
     * @return bool
     */
    public function isOwner($user = null);

    /**
     * Find an existing content lock by content ID or create a new one.
     *
     * @param  string|int  $contentId  The ID of the content to find or create a lock for.
     * @param  AuthenticatableContract|Model|null  $user  Optional. The user associated with the content lock. Default is null.
     * @return mixed The content lock instance.
     */
    public static function findOrCreate($contentId, $user = null);
}
