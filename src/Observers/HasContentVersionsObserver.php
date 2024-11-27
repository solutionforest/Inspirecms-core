<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use SolutionForest\InspireCms\Events\Content\DispatchContentVersion;
use SolutionForest\InspireCms\Models\Contracts\Base\HasContentVersions;

class HasContentVersionsObserver
{
    /**
     * Indicates whether the restoring process is currently active.
     *
     * @var bool
     */
    public static $restoring = false;

    /**
     * @param  HasContentVersions&Model  $model
     * @return void
     */
    public function saving($model)
    {
        // Skip state the versioning if restoring
        if (static::$restoring) {
            return;
        }
        // Preset the content version event
        $event = $model->exists ? 'updated' : 'created';
        $model->setVersioningEvent($event);
    }

    /**
     * @param  HasContentVersions&Model  $model
     * @return void
     */
    public function saved($model)
    {
        // Skip state the versioning if restoring
        if (static::$restoring) {
            return;
        }
        $this->dispatchContentVersioning($model);
    }

    /**
     * @param  HasContentVersions&Model  $model
     * @return void
     */
    public function deleting($model)
    {
        if (! $this->isSupportSoftDelete($model)) {
            $this->deleteContentVersions($model);
        }
    }

    /**
     * @param  HasContentVersions&Model  $model
     * @return void
     */
    public function forceDeleting($model)
    {
        $this->deleteContentVersions($model);
    }

    /**
     * @param  HasContentVersions&Model  $model
     * @return void
     */
    public function restoring($model)
    {
        // When restoring a model, an updated event is also fired.
        static::$restoring = true;
    }

    /**
     * @param  HasContentVersions&Model  $model
     * @return void
     */
    public function resotred($model)
    {
        // Once the model is restored, we need to put everything back
        // as before, in case a legitimate update event is fired
        static::$restoring = false;
    }

    protected function dispatchContentVersioning($model)
    {
        $model->preloadContentVersionData();

        // Unload the relations to prevent large amounts of unnecessary data from being serialized.
        event(new DispatchContentVersion($model->withoutRelations()));
    }

    protected function isSupportSoftDelete($model)
    {
        return in_array(SoftDeletes::class, class_uses($model));
    }

    protected function deleteContentVersions($model)
    {
        $model->contentVersions()->delete();
        $model->publishVersionLogs()->delete();
    }
}
