<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SolutionForest\InspireCms\Events\Content\GenerateSitemap;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Models\Contracts\Language;

class LanguageObserver
{
    /**
     * Handle "saving" event.
     *
     * @param  Language|Model  $model  The model instance being saving.
     * @return void
     */
    public function saving(Language | Model $model)
    {
        if (blank($model->route_pattern)) {
            $model->route_pattern = $model->code;
        }
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
     * Handle "updated" event.
     *
     * @param  Language|Model  $model  The model instance being updated.
     * @return void
     */
    public function updated(Language | Model $model)
    {
        event(new GenerateSitemap($model, 'updated'));
    }

    /**
     * Handle "deleting" event.
     *
     * @param  Language|Model  $model  The model instance being deleting.
     * @return void
     */
    public function deleting(Language | Model $model)
    {
        if ($model->is_default) {
            throw new \Exception('Cannot delete default language');
        }

        event(new GenerateSitemap($model, 'updated'));
        $this->clearCached();
    }

    protected function clearCached()
    {
        InspireCms::forgetCachedLanguages();
        InspireCms::forgetCachedNavigation();
    }
}
