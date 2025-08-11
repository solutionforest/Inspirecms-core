<?php

namespace SolutionForest\InspireCms\Observers;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SolutionForest\InspireCms\Events\Content\GenerateSitemap;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Models\Contracts\Language;

class LanguageObserver
{
    /**
     * @param  Language&Model  $model
     * @return void
     */
    public function saving($model)
    {
        // Set "is_default" of other languages as false if this model is changing to "default"
        if ($model->isDirty(['is_default']) && $model->is_default) {
            DB::transaction(function () use ($model) {
                $model->newQuery()
                    ->where('is_default', true)
                    ->whereKeyNot($model->getKey())
                    ->update(['is_default' => false]);
            });
        }

        $this->clearCached();
    }

    /**
     * @param  Language&Model  $model
     * @return void
     */
    public function updating($model)
    {
        //
    }

    /**
     * @param  Language&Model  $model
     * @return void
     */
    public function updated($model)
    {
        event(new GenerateSitemap(get_class($model), $model?->getKey(), 'updated'));
    }

    /**
     * @param  Language&Model  $model
     * @return void
     */
    public function deleting($model)
    {
        if ($model->is_default) {
            throw new Exception('Cannot delete default language');
        }

        event(new GenerateSitemap(get_class($model), $model?->getKey(), 'updated'));

        $this->clearCached();

        $this->deleteRelatedModels($model);
    }

    protected function clearCached()
    {
        InspireCms::forgetCachedLanguages();
        InspireCms::forgetCachedNavigation();
    }

    /**
     * @param  Language&Model  $model
     * @return void
     */
    protected function deleteRelatedModels($model)
    {
        $model->contentRoutes()->delete();
    }
}
