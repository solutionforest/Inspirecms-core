<?php

namespace SolutionForest\InspireCms\Base\Filament\Resources\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Alignment;

class BaseCreatePage extends CreateRecord
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

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label(__('inspirecms::actions.save.label'));
    }

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();

        if ($this->redirectToIndex()) {

            return $resource::getUrl('index');
        }

        return parent::getRedirectUrl();

    }

    public function getSubNavigation(): array
    {
        return static::getResource()::getRecordSubNavigation($this);
    }

    protected function redirectToIndex(): bool
    {
        return false;
    }
}
