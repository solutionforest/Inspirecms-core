<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;
use SolutionForest\InspireCms\Models\Contracts\FieldGroupable;

class FieldGroupableObserver
{
    /**
     * @param  FieldGroupable&Model  $model
     * @return void
     */
    public function created($model)
    {
        if ($model->groupabled instanceof DocumentType) {
            $model->groupabled->inheritingDocumentTypes()->each(function ($documentType) use ($model) {
                $documentType->inheritFieldGroupsFrom($model->groupabled);
            });
        }
    }

    /**
     * @param  FieldGroupable&Model  $model
     * @return void
     */
    public function deleting($model)
    {
        if ($model->groupabled instanceof DocumentType) {
            $model->groupabled->inheritingDocumentTypes()->each(function (DocumentType $documentType) use ($model) {
                $documentType->deteachInheritFieldGroupsFrom($model->groupabled);
            });
        }
    }
}
