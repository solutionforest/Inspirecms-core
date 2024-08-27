<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\PageResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Alignment;
use SolutionForest\InspireCms\Filament\Resources\Contents\PageResource;
use SolutionForest\InspireCms\Filament\Resources\Contents\PageResource\Concerns\CanBePublish;

class CreatePage extends CreateRecord
{
    use CanBePublish;

    public function getFormActionsAlignment(): string | Alignment
    {
        return Alignment::End;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getPublishFormAction('create'),
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    // protected function getForms(): array
    // {
    //     return [
    //         ...parent::getForms(),
    //         'publishForm' => $this->publishForm(static::getResource()::publishForm(
    //             $this->makeForm(),
    //         )),
    //     ];
    // }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label(__('inspirecms::inspirecms.actions.save_draft.label'))
            ->color('secondary');
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.page', PageResource::class);
    }
}
