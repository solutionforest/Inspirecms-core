<?php

namespace SolutionForest\InspireCms\Models\Pivot;

use SolutionForest\InspireCms\Models\Contracts\AllowedDocumentType as AllowedDocumentTypeContract;
use SolutionForest\InspireCms\Support\Base\Models\BasePivotModel;

class AllowedDocumentType extends BasePivotModel implements AllowedDocumentTypeContract 
{
    protected $table = 'document_type_allowed_document_type';

    protected $guarded = [];
}
