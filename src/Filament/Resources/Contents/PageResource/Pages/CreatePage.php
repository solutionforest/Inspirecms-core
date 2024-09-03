<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\PageResource\Pages;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Alignment;
use SolutionForest\InspireCms\Filament\Resources\Contents\PageResource;
use SolutionForest\InspireCms\Filament\Resources\Contents\PageResource\Concerns\CanBePublish;
use SolutionForest\InspireCms\Filament\Resources\Contents\PageResource\Contracts\HasPublishForm;

class CreatePage extends CreateRecord implements HasPublishForm
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
        return config('inspirecms.resources.page', PageResource::class);
    }

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();

        return $resource::getUrl('index');
    }
}
