<?php

namespace SolutionForest\InspireCms\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Base\Filament\Actions\Concerns\CanCustomizeAuthorizedGuardActionProcess;
use SolutionForest\InspireCms\Filament\Contracts\GuardAction;
use SolutionForest\InspireCms\Models\Contracts\Content;

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

        $this->hidden(fn (null | Model | Content $record) => is_null($record));

        $this->slideOver();

        $this->modalContent(fn (Model | Content $record, $action) => 
            view('inspirecms::filament.actions.content-history', [
                'record' => $record,
                'action' => $action,
            ])
        );

        $this->icon('heroicon-o-clock');

        $this->modalSubmitAction(false);
        $this->modalCancelAction(false);

        $this->modalWidth('6xl');

        $this->color('gray');

        $this->disabledForm();

        $this->registerModalActions([
            Action::make('toggleAvoidToClear')
                ->size('xs')
                ->hidden(fn ($arguments) => !isset($arguments['item']))
                ->label(fn ($arguments) => ($arguments['item']['avoidToClear'] ?? false) ? 'Avoid to clean' : 'Wait to clean')
                ->color(fn ($arguments) => ($arguments['item']['avoidToClear'] ?? false) ? 'gray' : 'danger')
                ->outlined()
                ->successNotificationTitle(fn ($arguments) => ($arguments['item']['avoidToClear'] ?? false) ? 'Now avoiding to clean' : 'Now waiting to clean')
                ->successNotification(fn (Notification $notification) => $notification
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->iconColor('warning')
                )
                ->action(function (array $arguments, Action $action, Model | Content $record) {
                    if (! isset($arguments['item']['id'])) {
                        return;
                    }

                    $contentVersion = $record->contentVersions()->find($arguments['item']['id']);
                    if (! $contentVersion) {
                        return;
                    }

                    $contentVersion->avoid_to_clean = ! $contentVersion->avoid_to_clean;
                    $contentVersion->save();


                    $action->success();
                }),
        ]);
    }
}
