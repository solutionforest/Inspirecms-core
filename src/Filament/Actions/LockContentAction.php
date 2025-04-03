<?php

namespace SolutionForest\InspireCms\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\InspireCmsConfig;

class LockContentAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'lockContent';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn () => __('inspirecms::buttons.lock_content.label'));

        $this->successNotificationTitle(fn () => __('inspirecms::buttons.lock_content.messages.success.title'));

        $this->icon(FilamentIcon::resolve('inspirecms::locked'));

        $this->model(InspireCmsConfig::getContentModelClass());

        $this->authorize('lock');

        $this->visible(function (?Model $record) {
            if (! $record) {
                return false;
            }
            if ($record->trashed()) {
                return false;
            }

            return ! $record->isLocked();
        });

        $this->action(function (Model $record, Action $action, $livewire) {
            $record->lock();
            $action->success();
        });
    }
}
