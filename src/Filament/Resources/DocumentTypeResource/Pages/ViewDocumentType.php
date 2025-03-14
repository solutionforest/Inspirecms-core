<?php

namespace SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\Pages;

use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseViewRecord;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\Concerns\DocumentTypeDetailTrait;
use SolutionForest\InspireCms\InspireCmsConfig;

class ViewDocumentType extends BaseViewRecord
{
    use DocumentTypeDetailTrait;

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('document_type', DocumentTypeResource::class);
    }
}
