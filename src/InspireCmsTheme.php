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
        return 'inspirecms';
    }

    public function register(Panel $panel): void
    {
        FilamentAsset::register([
            Theme::make('inspirecms', __DIR__ . '/../resources/dist/inspirecms.css'),
        ]);

        $panel
            ->font('DM Sans')
            ->colors([
                'danger' => Color::hex('#f44336'),
                'gray' => Color::hex('#5e5e5e'),
                'info' => Color::hex('#88B0BA'),
                'primary' => Color::hex('#B5834A'),
                'secondary' => Color::hex('#d2b492'),
                'success' => Color::hex('#76ae51'),
                'warning' => Color::hex('#f39e19'),
                
                'zinc' => Color::Zinc,
                'lime' => Color::Lime,
            ])
            ->theme('inspirecms')
            ->maxContentWidth('full');
    }

    public function boot(Panel $panel): void {}
}
