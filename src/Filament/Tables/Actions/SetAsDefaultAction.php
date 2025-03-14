<?php

namespace SolutionForest\InspireCms\Filament\Tables\Actions;

use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Actions\Action;

class SetAsDefaultAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'setAsDefault';
    }

    protected function setUp(): void
    {
        $this
            ->label(__('inspirecms::buttons.set_as_default.label'))
            ->color('primary')
            ->successNotification(fn (Notification $notification) => $notification
                ->title(__('inspirecms::buttons.set_as_default.messages.success.title'))
                ->body(__('inspirecms::buttons.set_as_default.messages.success.body'))
            )
            ->failureNotification(fn (Notification $notification) => $notification
                ->title(__('inspirecms::buttons.set_as_default.messages.failure.title'))
                ->body(__('inspirecms::buttons.set_as_default.messages.failure.body'))
            )
            ->icon(FilamentIcon::resolve('inspirecms::as_default'));
    }
}
