<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Pages;

use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseEditPage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Concerns\DocumentTypeDetailTrait;

class EditDocumentType extends BaseEditPage
{
    use DocumentTypeDetailTrait;

    public static function getResource(): string
    {
        return config('inspirecms.filament.resources.document_type', DocumentTypeResource::class);
    }
}
