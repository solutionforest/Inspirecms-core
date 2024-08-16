<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings\DocumentTypeResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use SolutionForest\InspireCms\Filament\Resources\Pages\EditWithDetailInfoPage;
use SolutionForest\InspireCms\Filament\Resources\Settings\DocumentTypeResource;

class EditDocumentType extends EditWithDetailInfoPage
{
    public function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
        ->label(__('inspirecms::inspirecms.actions.save.label'));
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.document_type', DocumentTypeResource::class);
    }
}
