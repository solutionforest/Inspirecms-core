<?php

namespace SolutionForest\InspireCms\Base\Filament\Resources\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Alignment;

class BaseViewPage extends ViewRecord
{
    public function getFormActionsAlignment(): string | Alignment
    {
        return 'end';
    }

    public function getActions(): array
    {
        return [
            EditAction::make()->iconButton(),
            DeleteAction::make()->iconButton(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label(__('inspirecms::buttons.save.label'));
    }
}
