<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\Schemas\Components;

use Filament\Forms\Components\DateTimePicker;
use Filament\Support\Facades\FilamentIcon;
use SolutionForest\InspireCms\Filament\Forms\Components\Actions\ResetAction;

class ContentPublishedAtDateTimePicker
{
    public static function make(): DateTimePicker
    {
        return DateTimePicker::make('published_at')
            ->label(__('inspirecms::resources/content.published_at.label'))
            ->native(false)
            ->prefixIcon('heroicon-m-calendar-date-range')
            ->suffixAction(ResetAction::make())
            ->hintIcon(FilamentIcon::resolve('inspirecms::info'), __('inspirecms::resources/content.published_at.hint'))
            ->default(now())
            ->required();
    }
}
