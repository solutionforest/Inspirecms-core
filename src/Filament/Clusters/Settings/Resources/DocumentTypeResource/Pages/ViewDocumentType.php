<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Pages;

use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseViewPage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Concerns\DocumentTypeDetailTrait;
use SolutionForest\InspireCms\InspireCmsConfig;

class ViewDocumentType extends BaseViewPage
{
    use DocumentTypeDetailTrait;

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('document_type', DocumentTypeResource::class);
    }
}
