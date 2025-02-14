<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Acitons;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\InspireCmsConfig;

class LockAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'lock';
    }

    protected function setUp(): void
    {
        // todo: add translation
        parent::setUp();

        $this->icon('heroicon-o-lock-closed');

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
