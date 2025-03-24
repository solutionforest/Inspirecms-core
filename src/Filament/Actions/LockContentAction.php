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
        return 'lockConent';
    }

    protected function setUp(): void
    {
        // todo: add translation
        parent::setUp();

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

        $this->successNotificationTitle('Locked');

        $this->action(function (Model $record, Action $action, $livewire) {
            $record->lock();
            $action->success();
        });
    }
}
