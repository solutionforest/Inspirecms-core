<?php

namespace SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\Pages;

use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseEditRecord;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\Concerns\DocumentTypeDetailTrait;
use SolutionForest\InspireCms\InspireCmsConfig;

class EditDocumentType extends BaseEditRecord
{
    use DocumentTypeDetailTrait;

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('document_type', DocumentTypeResource::class);
    }
}
