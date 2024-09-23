<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Alignment;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldResource;

class CreateField extends CreateRecord
{
    protected static bool $canCreateAnother = false;

    public function getFormActionsAlignment(): string | Alignment
    {
        return 'end';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label(__('inspirecms::actions.save.label'));
    }
    
    public static function getResource(): string
    {
        return config('inspirecms.resources.field', FieldResource::class);
    }
}
