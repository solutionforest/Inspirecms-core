<?php

namespace SolutionForest\InspireCms\Filament\TreeNode\Actions;

use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Support\TreeNodes\Actions\Action;
use SolutionForest\InspireCms\Support\TreeNodes\Contracts\HasModelExplorer;

class SetDefaultContentPageAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'set_default_content_page';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('inspirecms::resources/content.actions.set_default_content_page.label'));

        $this->icon('heroicon-o-viewfinder-circle');

        $this->authorize('setAsDefault');

        $this->model(InspireCmsConfig::getContentModelClass());

        $this->hidden(function ($itemKey, HasModelExplorer $livewire) {

            $item = filled($itemKey) ? $livewire->getCacheModelItemNode($itemKey) : [];

            if (! is_array($item)) {
                return true;
            }

            if (($item['documentTypeCat'] ?? null) != 'web') {
                return true;
            }

            return ($item['depth'] ?? 1) != 0;

        });

        $this->successNotificationTitle(__('inspirecms::resources/content.actions.set_default_content_page.notification.success.title'));

        $this->action(function ($model, $itemKey, Action $action) {

            if (($record = $model->find($itemKey))) {

                $record->setAsDefault();

                $action->success();
            }
        });
    }
}
