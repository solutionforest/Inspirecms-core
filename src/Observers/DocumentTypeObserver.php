<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Base\Enums\DocumentTypeType as DocumentTypeTypeEnum;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;

class DocumentTypeObserver
{
    /**
     * Handle "saving" event.
     *
     * @param  DocumentType|Model  $model  The model instance being saving.
     * @return void
     */
    public function saving(DocumentType | Model $model)
    {
        //region Set default value
        if (blank($model->type) || is_null($model->type)) {
            $model->type = DocumentTypeTypeEnum::Web->value;
        }
        //endregion Set default value

        if (! $model->canInheriting()) {
            $model->show_children_as_table = false;
        }
    }
}
