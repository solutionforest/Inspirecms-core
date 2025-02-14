<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use SolutionForest\InspireCms\Exceptions\UnauthorizedOwnerException;
use SolutionForest\InspireCms\InspireCmsConfig;

trait CanLockContent
{
    public function locked()
    {
        return $this->hasOne(InspireCmsConfig::getContentLockModelClass(), 'content_id');
    }

    public function lock($user = null)
    {
        return $this->locked()->getRelated()::findOrCreate($this->getKey(), $user);
    }

    public function isOwnerForLock($user = null)
    {
        $this->loadMissing('locked.owner');
        $lock = $this->locked;

        if (! $lock) {
            return false;
        }

        return $lock->isOwner($user);
    }

    public function unlock($user = null)
    {
        $this->loadMissing('locked.owner');
        $lock = $this->locked;

        if (! $lock) {
            return false;
        }

        if (! $lock->isOwner($user)) {
            throw UnauthorizedOwnerException::forContent($this->getKey());
        }

        return $lock->delete();
    }

    public function isLocked(): bool
    {
        $this->loadMissing('locked');

        return $this->locked != null;
    }
}
