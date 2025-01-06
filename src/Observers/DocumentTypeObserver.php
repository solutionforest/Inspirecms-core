<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SolutionForest\InspireCms\Base\Enums\DocumentTypeCategory as DocumentTypeCategoryEnum;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;

class DocumentTypeObserver
{
    /**
     * @param  DocumentType&Model  $model
     * @return void
     */
    public function saving($model)
    {
        // region Set default value
        if (blank($model->category) || is_null($model->category)) {
            $model->category = DocumentTypeCategoryEnum::Web->value;
        }
        // endregion Set default value

        if (! ($model->display_category?->canInheriting() ?? false)) {
            $model->show_as_table = false;
        }
    }

    /**
     * @param  DocumentType&Model  $model
     * @return void
     */
    public function deleting($model)
    {
        $content = $model->content()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ])->get();

        // Guard: If there is any content, then prevent deleting.
        if ($content->isNotEmpty()) {
            throw new \Exception('Cannot delete this document type because it has content.');
        }
    }
}
