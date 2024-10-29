<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;
use SolutionForest\InspireCms\Models\Contracts\FieldGroupable;

class FieldGroupableObserver
{
    /**
     * Handle "created" event.
     *
     * @param  FieldGroupable|Model  $model  The model instance being created.
     * @return void
     */
    public function created(FieldGroupable | Model $model)
    {
        if ($model->groupabled instanceof DocumentType) {
            $model->groupabled->inheritingDocumentTypes()->each(function ($documentType) use ($model) {
                $documentType->inheritFieldGroupsFrom($model->groupabled);
            });
        }
    }

    /**
     * Handle "deleting" event.
     *
     * @param  FieldGroupable|Model  $model  The model instance being deleting.
     * @return void
     */
    public function deleting(FieldGroupable | Model $model)
    {
        if ($model->groupabled instanceof DocumentType) {
            $model->groupabled->inheritingDocumentTypes()->each(function (DocumentType $documentType) use ($model) {
                $documentType->deteachInheritFieldGroupsFrom($model->groupabled);
            });
        }
    }
}
