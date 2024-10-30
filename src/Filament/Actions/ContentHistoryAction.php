<?php

namespace SolutionForest\InspireCms\Filament\Actions;

use Filament\Actions\Action;
use SolutionForest\InspireCms\Base\Filament\Actions\Concerns\CanCustomizeAuthorizedGuardActionProcess;
use SolutionForest\InspireCms\Filament\Contracts\GuardAction;

class ContentHistoryAction extends Action implements GuardAction
{
    use CanCustomizeAuthorizedGuardActionProcess;

    public static function getDefaultName(): ?string
    {
        return 'contentHistory';
    }

    public static function getPermissionName(): string
    {
        return 'action_view_content_history';
    }
    
    public static function getPermissionDisplayName(): string
    {
        return __('inspirecms::actions.content_history.permission_display_name');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn () => __('inspirecms::actions.content_history.label'));

        $this->hidden(fn ($record) => is_null($record));

        $this->slideOver();

        $this->modalContent(fn ($record) => view('inspirecms::filament.actions.content-history', [
            'record' => $record,
        ]));

        $this->icon('heroicon-o-clock');

        $this->modalSubmitAction(false);
        $this->modalCancelAction(false);

        $this->color('gray');

        $this->disabledForm();
    }
}
