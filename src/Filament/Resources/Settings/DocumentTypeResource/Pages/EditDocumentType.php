<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings\DocumentTypeResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;
use SolutionForest\InspireCms\Filament\Resources\Settings\DocumentTypeResource;

class EditDocumentType extends EditRecord
{
    public function getFormActionsAlignment(): string | Alignment
    {
        return 'end';
    }

    public function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label(__('inspirecms::actions.save.label'));
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.document_type', DocumentTypeResource::class);
    }
}
