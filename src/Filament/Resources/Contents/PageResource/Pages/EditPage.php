<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\PageResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use SolutionForest\InspireCms\Filament\Resources\Contents\PageResource;
use SolutionForest\InspireCms\Filament\Resources\Pages\Concerns\HasSaveAndPublishAction;
use SolutionForest\InspireCms\Filament\Resources\Pages\EditWithDetailInfoPage;

class EditPage extends EditWithDetailInfoPage
{
    use HasSaveAndPublishAction;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

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

    public function saveAndPublish()
    {
        // todo
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.page', PageResource::class);
    }
}
