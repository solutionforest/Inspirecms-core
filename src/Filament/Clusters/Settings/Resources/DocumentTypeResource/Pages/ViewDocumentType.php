<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Pages;

use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseViewPage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Concerns\DocumentTypeDetailTrait;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Concerns\DocumentTypeFormTrait;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Contracts\DocumentTypeForm;

class ViewDocumentType extends BaseViewPage implements DocumentTypeForm
{
    use DocumentTypeFormTrait;
    use DocumentTypeDetailTrait;

    public static function getResource(): string
    {
        return config('inspirecms.filament.resources.document_type', DocumentTypeResource::class);
    }
}
