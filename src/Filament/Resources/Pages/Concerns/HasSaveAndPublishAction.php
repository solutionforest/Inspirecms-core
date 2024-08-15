<?php

namespace SolutionForest\InspireCms\Filament\Resources\Pages\Concerns;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\IconPosition;
use SolutionForest\InspireCms\Filament\Actions\SaveAndPublishAction;

trait HasSaveAndPublishAction
{
    protected function getSaveAndPublishFormAction(): Action
    {
        return SaveAndPublishAction::make()
            ->label(__('inspirecms::inspirecms.actions.save_and_publish.label'))
            ->submit('saveAndPublish')
            ->icon('heroicon-o-globe-alt')
            ->color('primary');
    }

    protected function getSaveAndPublishGroupAction(array $actions): ActionGroup
    {
        return ActionGroup::make($actions)
            ->label(__('inspirecms::inspirecms.actions.save_and_publish.label'))
            ->color('primary')
            ->icon('heroicon-m-ellipsis-vertical')
            ->iconPosition(IconPosition::After)
            ->color('primary')
            ->button();
    }

    public function saveAndPublish()
    {
        // todo
    }
}
