<?php

namespace SolutionForest\InspireCms\Filament\TreeNode\Actions;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Support\TreeNodes\Actions\Action;

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

        $this->hidden(function (null | Content | Model $record) {
            if (is_null($record) || $record->is_default) {
                return true;
            }

            if (! $record instanceof Content) {
                throw new \Exception('The provided record is not an instance of the Content model.');
            }

            if ($record->documentType?->isDataType() == true) {
                return true;
            }

            $rootLevelKey = $record->getNestableTreeRootLevelParentId();

            $nestableTreeParentId = isset($record->nestable_tree_parent_id)
                ? $record->nestable_tree_parent_id
                : ($record->nestableTree?->parent_id ?? 0);

            return $nestableTreeParentId !== $rootLevelKey;
        });

        $this->successNotificationTitle(__('inspirecms::resources/content.actions.set_default_content_page.notification.success.title'));

        $this->action(function (Content | Model $record, Action $action) {
            $record->setAsDefault();

            $action->success();
        });
    }
}
