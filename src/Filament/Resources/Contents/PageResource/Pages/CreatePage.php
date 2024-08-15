<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\PageResource\Pages;

use Filament\Actions\Action;
use SolutionForest\InspireCms\Filament\Resources\Contents\PageResource;
use SolutionForest\InspireCms\Filament\Resources\Pages\Concerns\HasSaveAndPublishAction;
use SolutionForest\InspireCms\Filament\Resources\Pages\CreateWithDetailInfoPage;

class CreatePage extends CreateWithDetailInfoPage
{
    use HasSaveAndPublishAction;

    protected function getFormActions(): array
    {
        return [
            $this->getSaveAndPublishGroupAction([
                $this->getSaveAndPublishFormAction(),
                $this->getCreateFormAction(),
            ]),
            $this->getCancelFormAction(),
        ];
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label(__('inspirecms::inspirecms.actions.save.label'));
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.page', PageResource::class);
    }
}
