<?php

namespace SolutionForest\InspireCms\Base\Filament\Resources\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;

class BaseEditPage extends EditRecord
{
    public function getFormActionsAlignment(): string | Alignment
    {
        return 'end';
    }

    public function getActions(): array
    {
        return [
            ViewAction::make()->iconButton(),
            DeleteAction::make()->iconButton(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label(__('inspirecms::buttons.save.label'));
    }
}
