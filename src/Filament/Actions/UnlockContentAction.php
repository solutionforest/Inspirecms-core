<?php

namespace SolutionForest\InspireCms\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Exceptions\UnauthorizedOwnerException;
use SolutionForest\InspireCms\InspireCmsConfig;

class UnlockContentAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'unlockContent';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn () => __('inspirecms::buttons.unlock_content.label'));

        $this->successNotificationTitle(fn () => __('inspirecms::buttons.unlock_content.messages.success.title'));

        $this->failureNotificationTitle(fn () => __('inspirecms::messages.something_went_wrong'));

        $this->icon(FilamentIcon::resolve('inspirecms::unlocked'));

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

        $this->action(function (Model $record, Action $action, $livewire) {
            try {
                if ($record->unlock() == true) {
                    $action->success();

                    return;
                }
            } catch (UnauthorizedOwnerException $th) {
                Notification::make()
                    ->title(__('inspirecms::buttons.unlock_content.messages.not_owner_error.title'))
                    ->body(__('inspirecms::buttons.unlock_content.messages.not_owner_error.body'))
                    ->danger()
                    ->send();

                return;
            }
            $action->failure();
        });
    }
}
