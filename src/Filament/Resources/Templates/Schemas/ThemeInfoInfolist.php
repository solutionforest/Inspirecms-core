<?php

namespace SolutionForest\InspireCms\Filament\Resources\Templates\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use SolutionForest\InspireCms\Filament\Resources\Templates\Actions\ChangeThemeAction;
use SolutionForest\InspireCms\Helpers\TemplateHelper;

class ThemeInfoInfolist
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->columns(1)
            ->components([

                TextEntry::make('current_theme')
                    ->weight('bold')
                    ->color('primary')
                    ->label(__('inspirecms::inspirecms.current_xxx', ['name' => __('inspirecms::inspirecms.theme')]))
                    ->state(function () {
                        return inspirecms_templates()->getCurrentTheme() ?? TemplateHelper::getDefaultTemplateTheme();
                    })
                    ->hintAction(
                        ChangeThemeAction::make()
                    ),

                TextEntry::make('layout')
                    ->label(__('inspirecms::inspirecms.layout'))
                    ->state(function ($get) {
                        $currentTheme = $get('current_theme');

                        $layoutPath = inspirecms_templates()->getThemeDefaultLayoutPath($currentTheme);

                        return str($layoutPath ? str_replace(base_path(), '', $layoutPath) : '')
                            ->replace('\\', '/')
                            ->trim('/')
                            ->toString();
                    })
                    ->fontFamily(FontFamily::Mono)
                    ->size('xs')
                    ->placeholder(fn () => strval(__('inspirecms::inspirecms.n/a')))
                    ->extraAttributes(['class' => 'overflow-x-auto overflow-y-hidden']),
            ]);
    }
}
