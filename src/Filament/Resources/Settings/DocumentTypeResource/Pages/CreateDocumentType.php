<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings\DocumentTypeResource\Pages;

use Filament\Actions\Action;
use SolutionForest\InspireCms\Filament\Resources\Pages\CreateWithDetailInfoPage;
use SolutionForest\InspireCms\Filament\Resources\Settings\DocumentTypeResource;

class CreateDocumentType extends CreateWithDetailInfoPage
{
    protected static bool $canCreateAnother = false;
    
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label(__('inspirecms::inspirecms.actions.save.label'));
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.document_type', DocumentTypeResource::class);
    }
}
