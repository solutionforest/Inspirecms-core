<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Alignment;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Concerns\CanBePublish;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Contracts\HasPublishForm;

abstract class BaseContentCreatePage extends CreateRecord implements HasPublishForm
{
    use CanBePublish;

    public function getFormActionsAlignment(): string | Alignment
    {
        return 'end';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getPublishFormAction('create'),
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label(__('inspirecms::actions.save_draft.label'))
            ->color('secondary');
    }

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();

        return $resource::getUrl('index');
    }
}
