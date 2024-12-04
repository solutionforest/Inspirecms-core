<?php

namespace SolutionForest\InspireCms\Filament\TreeNode\Actions;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Base\Filament\Actions\Concerns\CanCustomizeAuthorizedGuardActionProcess;
use SolutionForest\InspireCms\Filament\Contracts\GuardAction;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Support\TreeNodes\Actions\Action;

class SetDefaultContentPageAction extends Action implements GuardAction
{
    use CanCustomizeAuthorizedGuardActionProcess;

    public static function getDefaultName(): ?string
    {
        return 'set_default_content_page';
    }

    public static function getPermissionName(): string
    {
        return 'action_set_default_content_page';
    }

    public static function getPermissionDisplayName(): string
    {
        return __('inspirecms::actions.set_default_content_page.permission_display_name');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('inspirecms::actions.set_default_content_page.label'));

        $this->icon('heroicon-o-globe-alt');

        $this->hidden(function (null | Content | Model $record) {
            if (is_null($record) || $record->is_default) {
                return true;
            }

            if (! $record instanceof Content) {
                throw new \Exception('The provided record is not an instance of the Content model.');
            }

            $rootLevelKey = $record->getNestableTreeRootLevelParentId();

            $nestableTreeParentId = isset($record->nestable_tree_parent_id)
                ? $record->nestable_tree_parent_id
                : ($record->nestableTree?->parent_id ?? 0);

            return $nestableTreeParentId !== $rootLevelKey;
        });

        $this->successNotificationTitle(__('inspirecms::actions.set_default_content_page.notifications.success.title'));

        $this->action(function (Content | Model $record, Action $action) {
            $record->is_default = true;
            $record->save();

            $action->success();
        });
    }
}
