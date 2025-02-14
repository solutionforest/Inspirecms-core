<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Acitons;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Exceptions\UnauthorizedOwnerException;
use SolutionForest\InspireCms\InspireCmsConfig;

class UnlockAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'unlock';
    }

    protected function setUp(): void
    {
        // todo: add translation
        parent::setUp();

        $this->icon('heroicon-o-lock-open');

        $this->model(InspireCmsConfig::getContentModelClass());

        $this->authorize('lock');

        $this->visible(function (?Model $record) {
            if (! $record) {
                return false;
            }
            if ($record->trashed()) {
                return false;
            }
            return $record->isLocked() && $record->isOwnerForLock();
        });

        $this->successNotificationTitle('Unlocked');

        $this->action(function (Model $record, Action $action, $livewire) {
            try {
                if ($record->unlock() == true) {
                    $action->success();
                    return;
                }
            } catch (UnauthorizedOwnerException $th) {
                Notification::make()
                    ->title('Unlock failed')
                    ->body('You are not the owner of the lock.')
                    ->danger()
                    ->send();
                return;
            }
            $action->failure();
        });
    }
}
