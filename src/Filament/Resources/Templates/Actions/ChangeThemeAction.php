<?php

namespace SolutionForest\InspireCms\Filament\Resources\Templates\Actions;

use Filament\Actions\Action;
use Filament\Support\Facades\FilamentIcon;
use SolutionForest\InspireCms\Events\Template\ChangeTheme;
use SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\Components\TemplateThemeSelector;
use SolutionForest\InspireCms\Helpers\TemplateHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

class ChangeThemeAction
{
    public static function make(): Action
    {
        return Action::make('changeTheme')
            ->label(__('inspirecms::buttons.change_theme.label'))
            ->icon(FilamentIcon::resolve('inspirecms::edit'))
            ->link()
            ->color('gray')
            ->fillForm(['theme' => inspirecms_templates()->getCurrentTheme() ?? TemplateHelper::getDefaultTemplateTheme()])
            ->schema([
                TemplateThemeSelector::make(),
            ])
            ->successNotificationTitle(__('inspirecms::buttons.change_theme.messages.success.title'))
            ->failureNotificationTitle(__('inspirecms::buttons.change_theme.messages.failure.title'))
            ->action(function (array $data, $component, Action $action) {

                $oldTheme = $component->getState();
                $newTheme = $data['theme'];

                // Is different theme
                if ($oldTheme !== $newTheme) {

                    InspireCmsConfig::getKeyValueModelClass()::setKeyValue(
                        TemplateHelper::getCurrentThemeKey(),
                        $newTheme
                    );

                    event(new ChangeTheme($oldTheme, $newTheme));

                    inspirecms_templates()->resetCurrentTheme();

                    $action->success();

                }
            });
    }
}
