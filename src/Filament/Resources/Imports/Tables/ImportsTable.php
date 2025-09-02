<?php

namespace SolutionForest\InspireCms\Filament\Resources\Imports\Tables;

use Carbon\Carbon;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use SolutionForest\InspireCms\Base\Enums\ImportStatus;
use SolutionForest\InspireCms\Filament\Resources\Imports\Schemas\ImportForm;
use SolutionForest\InspireCms\Filament\Resources\Imports\Schemas\ImportInfolist;

class ImportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon(FilamentIcon::resolve('inspirecms::upload'))
            ->emptyStateHeading(__('inspirecms::resources/import.empty_state.heading'))
            ->emptyStateDescription(__('inspirecms::resources/import.empty_state.description'))
            ->modelLabel(__('inspirecms::inspirecms.import'))
            ->columns([
                TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id')),
                TextColumn::make('file_name')
                    ->label(__('inspirecms::resources/import.file_name.label'))
                    ->fontFamily('mono'),
                TextColumn::make('display_status')
                    ->label(__('inspirecms::resources/import.status.label'))
                    ->badge()
                    ->iconColor(function ($state) {
                        if ($state instanceof ImportStatus) {
                            return $state->getColor();
                        }

                        return null;
                    }),
                TextColumn::make('available_at')
                    ->label(__('inspirecms::resources/import.available_at.label'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('inspirecms::inspirecms.created_at'))
                    ->sortable(),
                TextColumn::make('clear_at')
                    ->label(__('inspirecms::resources/import.clear_at.label'))
                    ->formatStateUsing(fn (?Carbon $state) => $state?->diffForHumans()),
                TextColumn::make('created_by')
                    ->label(__('inspirecms::inspirecms.created_by'))
                    ->getStateUsing(fn ($record) => $record->author?->email)
                    ->description(fn ($record) => $record->author?->name, 'above')
                    ->icon(FilamentIcon::resolve('inspirecms::email'))
                    ->copyable(),

            ])
            ->recordAction('view')
            ->headerActions([
                CreateAction::make()
                    ->createAnother(false)
                    ->modalWidth('7xl')
                    ->stickyModalHeader()->stickyModalHeader()
                    ->slideOver()
                    ->label(__('inspirecms::buttons.import.label'))
                    ->modalSubmitActionLabel(__('inspirecms::buttons.import.label'))
                    ->modalHeading(__('inspirecms::buttons.import.heading'))
                    ->schema(fn (Schema $schema) => ImportForm::configure($schema)),
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->slideOver()
                    ->schema(fn (Schema $schema) => ImportInfolist::configure($schema)),
            ]);
    }
}
