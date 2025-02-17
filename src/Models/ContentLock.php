<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\ContentLock as ContentLockContract;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;

class ContentLock extends BaseModel implements ContentLockContract
{
    protected $guarded = [];

    protected $primaryKey = 'content_id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'locked_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->morphTo();
    }

    public function isOwner($user = null)
    {
        $user = $this->retrieveOwner($user);

        return $this->owner_id === $user->getKey() &&
            $this->owner_type === $user->getMorphClass();
    }

    public static function findOrCreate($contentId, $user = null)
    {
        $lock = static::find($contentId);

        if (! $lock) {
            $lock = new static([
                'content_id' => $contentId,
                'locked_at' => now(),
            ]);

            $lock->owner()->associate($lock->retrieveOwner($user));

            $lock->save();
        }

        return $lock;
    }

    protected function retrieveOwner($user = null)
    {
        $user ??= auth()->user();

        if (! $user) {
            throw new \Exception('User not authenticated');
        }

        if (! ($user instanceof AuthenticatableContract || $user instanceof Model)) {
            throw new \Exception('User must implement AuthenticatableContract');
        }

        return $user;
    }
}
