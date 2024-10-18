<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\Content;

class ContentObserver
{
    /**
     * Handle the product "creating" event.
     *
     * @return void
     */
    public function creating(Content | Model $content)
    {
        //region Set the parent ID to the fallback parent ID if it is blank
        if (blank($content->{$content->getNestableParentIdColumn()})) {
            $content->{$content->getNestableParentIdColumn()} = $content->getNestableRootValue();
        }
        //endregion
    }

    /**
     * Handle the product "created" event.
     *
     * @return void
     */
    public function created(Content | Model $content)
    {
        //
    }

    /**
     * Handle the product "updated" event.
     *
     * @return void
     */
    public function updated(Content | Model $content)
    {
        //
    }

    /**
     * Handle the product "deleting" event.
     *
     * @return void
     */
    public function deleting(Content | Model $content)
    {
        $content->children()->delete();
    }

    /**
     * Handle the product "deleted" event.
     *
     * @return void
     */
    public function deleted(Content | Model $content)
    {
        //
    }

    /**
     * Handle the product "forceDeleting" event.
     *
     * @return void
     */
    public function forceDeleting(Content | Model $content)
    {
        $content->children()->forceDelete();
    }
}
