<?php

namespace SolutionForest\InspireCms\Filament\Resources\Exports\Tables;

use Carbon\Carbon;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Filament\Resources\Exports\Schemas\ExportForm;
use SolutionForest\InspireCms\Filament\Resources\Exports\Schemas\ExportInfolist;

class ExportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon(FilamentIcon::resolve('inspirecms::download'))
            ->emptyStateHeading(__('inspirecms::resources/export.empty_state.heading'))
            ->emptyStateDescription(__('inspirecms::resources/export.empty_state.description'))
            ->modelLabel(__('inspirecms::inspirecms.export'))
            ->columns([
                TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id')),
                TextColumn::make('display_exporter')
                    ->label(__('inspirecms::resources/export.exporter.label')),
                TextColumn::make('display_status')
                    ->label(__('inspirecms::resources/export.status.label'))
                    ->badge()
                    ->tooltip(function ($record) {
                        return $record->failed_at ?? $record->finished_at ?? null;
                    }),
                TextColumn::make('created_at')
                    ->label(__('inspirecms::inspirecms.created_at'))
                    ->sortable(),
                TextColumn::make('created_by')
                    ->label(__('inspirecms::inspirecms.created_by'))
                    ->getStateUsing(fn ($record) => $record->author?->email)
                    ->description(fn ($record) => $record->author?->name, 'above')
                    ->icon(FilamentIcon::resolve('inspirecms::email'))
                    ->copyable(),
                TextColumn::make('clear_at')
                    ->label(__('inspirecms::resources/export.clear_at.label'))
                    ->formatStateUsing(fn (?Carbon $state) => $state?->diffForHumans()),
            ])
            ->recordAction('view')
            ->headerActions([
                CreateAction::make()
                    ->createAnother(false)
                    ->modalWidth('5xl')
                    ->stickyModalHeader()->stickyModalHeader()
                    ->slideOver()
                    ->schema(fn (Schema $schema) => ExportForm::configure($schema))
                    ->label(__('inspirecms::buttons.export.label'))
                    ->modalSubmitActionLabel(__('inspirecms::buttons.export.label'))
                    ->successNotificationTitle(__('inspirecms::resources/export.notification.place_queue_success.title'))
                    ->failureNotificationTitle(__('inspirecms::resources/export.notification.place_queue_failue.title'))
                    ->failureNotification(fn (Notification $notification) => $notification->warning())
                    ->using(function (CreateAction $action, array $data, string $model) {

                        $user = auth()->user();
                        $exporter = $data['exporter'] ?? null;

                        if (! $user || ! filled($exporter)) {
                            $action->sendFailureNotification();

                            return $action->cancel();
                        }
                        $export = app($model, ['attributes' => Arr::only($data, ['exporter', 'payload'])]);
                        $export->author()->associate($user);
                        $export->save();

                        return $export;
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->slideOver()
                    ->authorize(null)
                    ->schema(fn (Schema $schema) => ExportInfolist::configure($schema)),
            ]);
    }
}
