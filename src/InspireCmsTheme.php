<?php

namespace SolutionForest\InspireCms;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Assets\Theme;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;

class InspireCmsTheme implements Plugin
{
    public function getId(): string
    {
        return 'inspirecms-core';
    }

    public function register(Panel $panel): void
    {
        FilamentAsset::register([
            Theme::make('inspirecms-core', __DIR__ . '/../resources/dist/inspirecms-core.css'),
        ]);

        $panel
            ->font('DM Sans')
            ->colors([
                'danger' => Color::hex('#f44336'),
                'gray' => Color::hex('#5e5e5e'),
                'info' => Color::hex('#e5cda4'),
                'primary' => Color::hex('#D7AC52'),
                'success' => Color::hex('#76ae51'),
                'warning' => Color::hex('#f39e19'),
            ])
            ->theme('inspirecms-core')
            ->maxContentWidth('full');
    }

    public function boot(Panel $panel): void {}
}
