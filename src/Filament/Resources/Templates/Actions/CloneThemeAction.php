<?php

namespace SolutionForest\InspireCms\Filament\Resources\Templates\Actions;

use Closure;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentIcon;
use SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\Components\TemplateThemeSelector;
use SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\Components\ThemeInput;
use Throwable;

class CloneThemeAction
{
    public static function make(): Action
    {
        return Action::make('cloneTheme')
            ->icon(FilamentIcon::resolve('inspirecms::clone'))
            ->modalIcon(FilamentIcon::resolve('inspirecms::clone'))
            ->color('gray')
            ->label(__('inspirecms::buttons.clone_theme.label'))
            ->successNotificationTitle(__('inspirecms::buttons.clone_theme.messages.success.title'))
            ->failureNotificationTitle(__('inspirecms::buttons.clone_theme.messages.failure.title'))
            ->schema([

                ThemeInput::make()
                    ->different('source_theme')
                    ->rules([
                        fn (): Closure => function (string $attribute, $value, Closure $fail) {
                            if (filled($value) && inspirecms_templates()->isThemeExists($value)) {
                                $fail(__('inspirecms::messages.theme_already_exists'));
                            }
                        },
                    ]),

                TemplateThemeSelector::make()
                    ->statePath('source_theme')
                    ->label(__('inspirecms::resources/template.source_theme.label'))
                    ->validationAttribute(strtolower(__('inspirecms::resources/template.source_theme.label')))
                    ->inlineLabel()
                    ->required(),
            ])
            ->action(function (array $data, Action $action) {

                $sourceTheme = $data['source_theme'];
                $newTheme = $data['theme'];

                if ($sourceTheme === $newTheme) {
                    $action->failureNotification(fn (Notification $notification) => $notification
                        ->body(__('inspirecms::messages.same_theme_name_already_exists')));
                    $action->failure();

                    return;
                }

                if (inspirecms_templates()->isThemeExists($newTheme)) {
                    $action->failureNotification(fn (Notification $notification) => $notification
                        ->body(__('inspirecms::messages.theme_already_exists')));
                    $action->failure();

                    return;
                }

                try {

                    inspirecms_templates()->cloneTheme($sourceTheme, $newTheme);

                    $action->success();

                } catch (Throwable $th) {
                    $action->failureNotification(fn (Notification $notification) => $notification
                        ->body(__('inspirecms::messages.something_went_wrong')));
                    $action->failure();
                }

            });
    }
}
