<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use SolutionForest\InspireCms\Events\Content\DispatchContentVersion;
use SolutionForest\InspireCms\Events\Content\GenerateSitemap;
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
     * Handle the saving event.
     *
     * @param  \SolutionForest\InspireCms\Models\Contracts\Base\HasContentVersions  $model
     * @return void
     */
    public function saving(HasContentVersions | Model $model)
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
     * Handle the saved event.
     *
     * @param  \SolutionForest\InspireCms\Models\Contracts\Base\HasContentVersions  $model
     * @return void
     */
    public function saved(HasContentVersions | Model $model)
    {
        // Skip state the versioning if restoring
        if (static::$restoring) {
            return;
        }
        $this->dispatchContentVersioning($model);
    }

    /**
     * Handle the deleting event.
     *
     * @param  \SolutionForest\InspireCms\Models\Contracts\Base\HasContentVersions  $model
     * @return void
     */
    public function deleting(HasContentVersions | Model $model)
    {
        $this->dispatchGenerateSitemap($model, 'deleting');

        if (! $this->isSupportSoftDelete($model)) {
            $this->deleteContentVersions($model);
        }
    }

    /**
     * Handle the forceDeleting event.
     *
     * @param  \SolutionForest\InspireCms\Models\Contracts\Base\HasContentVersions  $model
     * @return void
     */
    public function forceDeleting(HasContentVersions | Model $model)
    {
        $this->deleteContentVersions($model);
    }

    /**
     * Handle the restoring event.
     *
     * @param  \SolutionForest\InspireCms\Models\Contracts\Base\HasContentVersions  $model
     * @return void
     */
    public function restoring(HasContentVersions | Model $model)
    {
        // When restoring a model, an updated event is also fired.
        static::$restoring = true;

        $this->dispatchGenerateSitemap($model, 'restoring');
    }

    /**
     * Handle the resotred event.
     *
     * @param  \SolutionForest\InspireCms\Models\Contracts\Base\HasContentVersions  $model
     * @return void
     */
    public function resotred(HasContentVersions | Model $model)
    {
        // Once the model is restored, we need to put everything back
        // as before, in case a legitimate update event is fired
        static::$restoring = false;
    }

    protected function dispatchContentVersioning(HasContentVersions | Model $model)
    {
        $model->preloadContentVersionData();

        // Unload the relations to prevent large amounts of unnecessary data from being serialized.
        event(new DispatchContentVersion($model->withoutRelations()));
    }

    protected function dispatchGenerateSitemap(HasContentVersions | Model $model, string $event)
    {
        // Unload the relations to prevent large amounts of unnecessary data from being serialized.
        event(new GenerateSitemap($model->withoutRelations(), $event));
    }

    protected function isSupportSoftDelete(HasContentVersions | Model $model)
    {
        return in_array(SoftDeletes::class, class_uses($model));
    }

    protected function deleteContentVersions(HasContentVersions | Model $model)
    {
        $model->contentVersions()->delete();
        $model->publishVersionLogs()->delete();
    }
}
