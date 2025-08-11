<?php

namespace SolutionForest\InspireCms\Filament\Resources\Templates\Actions;

use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\TemplateUsageInfolist;

class ViewUsageAction
{
    public static function make()
    {
        return Action::make('viewUsage')
            ->label(__('inspirecms::buttons.view_usage.label'))
            ->modalHeading(fn (Action $action, $record, ?Table $table) => __('inspirecms::buttons.view_usage.heading', ['name' => ($record ? $action->getRecordTitle($record) : null) ?? $table?->getModelLabel()]))
            ->modalSubmitAction(fn () => false) // Disable the form submission
            ->color('gray')
            ->icon(FilamentIcon::resolve('actions::view-action') ?? 'heroicon-m-eye')
            ->schema(
                fn (Schema $schema) => TemplateUsageInfolist::configure($schema)
            );
    }
}
