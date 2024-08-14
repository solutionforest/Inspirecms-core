<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\PageResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use SolutionForest\InspireCms\Filament\Actions\SaveAndPublishAction;
use SolutionForest\InspireCms\Filament\Resources\Contents\PageResource;
use SolutionForest\InspireCms\Filament\Resources\Pages\EditWithDetailInfoPage;

class EditPage extends EditWithDetailInfoPage
{
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [

            ActionGroup::make([
                $this->getSaveAndPublishFormAction(),
                $this->getSaveFormAction(),
            ])
                ->label(__('inspirecms-core::inspirecms-core.actions.save_and_publish.label'))
                ->color('primary')
                ->icon('heroicon-m-ellipsis-vertical')
                ->iconPosition('after')
                ->color('primary')
                ->button(),

            $this->getCancelFormAction(),
        ];
    }

    protected function getSaveAndPublishFormAction(): Action
    {
        return SaveAndPublishAction::make()
            ->label(__('inspirecms-core::inspirecms-core.actions.save_and_publish.label'))
            ->submit('saveAndPublish');
    }

    public function saveAndPublish()
    {
        // todo
    }

    public static function getResource(): string
    {
        return config('inspirecms-core.resources.page', PageResource::class);
    }
}
