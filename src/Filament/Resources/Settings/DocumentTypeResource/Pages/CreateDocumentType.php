<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings\DocumentTypeResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Alignment;
use SolutionForest\InspireCms\Filament\Resources\Settings\DocumentTypeResource;

class CreateDocumentType extends CreateRecord
{
    protected static bool $canCreateAnother = false;

    public function getFormActionsAlignment(): string | Alignment
    {
        return Alignment::End;
    }

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
