<?php

namespace SolutionForest\InspireCms\Filament\TreeNode\Actions;

use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Support\TreeNodes\Actions\Action;

class DeleteContentItemAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'delete_content_item';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-actions::delete.single.label'));

        $this->authorize('delete');

        $this->modalHeading(fn (): string => __('filament-actions::delete.single.label'));

        $this->modalSubmitActionLabel(__('filament-actions::delete.single.modal.actions.delete.label'));

        $this->successNotificationTitle(__('filament-actions::delete.single.notifications.deleted.title'));

        $this->color('danger');

        $this->groupedIcon(FilamentIcon::resolve('actions::delete-action.grouped') ?? 'heroicon-m-trash');

        $this->requiresConfirmation();

        $this->modalIcon(FilamentIcon::resolve('actions::delete-action.modal') ?? 'heroicon-o-trash');

        $this->hidden(static function (?Model $record): bool {
            if (! $record) {
                return true;
            }

            if (! method_exists($record, 'trashed')) {
                return false;
            }

            return $record->trashed();
        });

        $this->action(function (?Model $record, Action $action): void {

            if (! $record) {

                return;
            }

            $result = $record->delete();

            if (! $result) {
                $action->failure();

                return;
            }

            $action->success();
        });
    }
}
