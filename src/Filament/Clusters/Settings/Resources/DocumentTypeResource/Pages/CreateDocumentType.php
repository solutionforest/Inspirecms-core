<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Pages;

use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseCreatePage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Concerns\DocumentTypeFormTrait;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Contracts\DocumentTypeForm;

class CreateDocumentType extends BaseCreatePage implements DocumentTypeForm
{
    use DocumentTypeFormTrait;

    public static function getResource(): string
    {
        return config('inspirecms.filament.resources.document_type', DocumentTypeResource::class);
    }
}
