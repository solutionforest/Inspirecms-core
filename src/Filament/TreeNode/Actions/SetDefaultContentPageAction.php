<?php

namespace SolutionForest\InspireCms\Filament\TreeNode\Actions;

use Filament\Support\Facades\FilamentIcon;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Support\TreeNodes\Actions\Action;
use SolutionForest\InspireCms\Support\TreeNodes\Contracts\HasModelExplorer;

class SetDefaultContentPageAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'setDefaultContentPage';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('inspirecms::buttons.set_default_content_page.label'));

        $this->icon(FilamentIcon::resolve('inspirecms::as_default'));

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

        $this->successNotificationTitle(__('inspirecms::buttons.set_default_content_page.messages.success.title'));

        $this->action(function (string $model, $itemKey, Action $action) {

            if (($record = $model::find($itemKey))) {

                $record->setAsDefault();

                $action->success();
            }
        });
    }
}
