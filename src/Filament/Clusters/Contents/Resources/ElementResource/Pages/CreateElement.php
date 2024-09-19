<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\ElementResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Alignment;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\ElementResource;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource\Concerns\CanBePublish;

class CreateElement extends CreateRecord
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

    public static function getResource(): string
    {
        return config('inspirecms.resources.element', ElementResource::class);
    }

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();

        return $resource::getUrl('index');
    }
}
