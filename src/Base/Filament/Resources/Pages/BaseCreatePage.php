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

        // Order: edit, view, index
        if (! $this->redirectToIndex()) {

            if ($resource::hasPage('edit') && $resource::canEdit($this->getRecord())) {
                return $resource::getUrl('edit', ['record' => $this->getRecord(), ...$this->getRedirectUrlParameters()]);
            }

            if ($resource::hasPage('view') && $resource::canView($this->getRecord())) {
                return $resource::getUrl('view', ['record' => $this->getRecord(), ...$this->getRedirectUrlParameters()]);
            }
        }

        return $resource::getUrl('index');

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
