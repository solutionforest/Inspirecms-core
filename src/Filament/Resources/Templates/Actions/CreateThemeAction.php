<?php

namespace SolutionForest\InspireCms\Filament\Resources\Templates\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentIcon;
use SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\Components\ThemeInput;
use Throwable;

class CreateThemeAction
{
    public static function make(): Action
    {
        return Action::make('createTheme')
            ->icon(FilamentIcon::resolve('inspirecms::add'))
            ->label(__('inspirecms::buttons.create_theme.label'))
            ->successNotificationTitle(__('inspirecms::buttons.create_theme.messages.success.title'))
            ->failureNotificationTitle(__('inspirecms::buttons.create_theme.messages.failure.title'))
            ->schema([
                ThemeInput::make(),
            ])
            ->action(function (array $data, Action $action) {
                $theme = $data['theme'] ?? '';

                if (blank($theme)) {
                    $action->failureNotification(fn (Notification $notification) => $notification
                        ->body(__('inspirecms::messages.something_went_wrong')));
                    $action->failure();

                    return;
                }

                if (inspirecms_templates()->isThemeExists($theme)) {
                    $action->failureNotification(fn (Notification $notification) => $notification
                        ->body(__('inspirecms::messages.theme_already_exists')));
                    $action->failure();

                    return;
                }

                try {
                    inspirecms_templates()->createTheme($theme);

                    $action->success();

                } catch (Throwable $th) {
                    $action->failureNotification(fn (Notification $notification) => $notification
                        ->body(__('inspirecms::messages.something_went_wrong')));
                    $action->failure();
                }
            });
    }
}
