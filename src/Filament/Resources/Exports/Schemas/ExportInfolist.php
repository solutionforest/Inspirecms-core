<?php

namespace SolutionForest\InspireCms\Filament\Resources\Exports\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Filament\Infolists\Components\JsonEntry;
use SolutionForest\InspireCms\Filament\Resources\Exports\Actions\DownloadExportAction;

class ExportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        TextEntry::make('id')
                            ->label(__('inspirecms::inspirecms.id'))
                            ->inlineLabel(),
                        TextEntry::make('display_status')
                            ->inlineLabel()
                            ->label(__('inspirecms::resources/export.status.label'))
                            ->badge()
                            ->tooltip(function ($record) {
                                return $record->failed_at ?? $record->finished_at ?? null;
                            }),
                        TextEntry::make('file_name')
                            ->label(__('inspirecms::resources/export.result.label'))
                            ->inlineLabel()
                            ->fontFamily('mono')
                            ->afterContent(DownloadExportAction::make()),
                    ]),

                Group::make()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('inspirecms::inspirecms.created_at'))
                            ->inlineLabel()
                            ->since()
                            ->dateTimeTooltip(),
                        TextEntry::make('finished_at')
                            ->label(__('inspirecms::resources/export.finished_at.label'))
                            ->inlineLabel(),
                        TextEntry::make('failed_at')
                            ->label(__('inspirecms::resources/export.failed_at.label'))
                            ->inlineLabel(),
                        TextEntry::make('clear_at')
                            ->weight('bold')
                            ->label(__('inspirecms::resources/export.clear_at.label'))
                            ->inlineLabel()
                            ->since()
                            ->dateTimeTooltip(),
                    ]),

                TextEntry::make('created_by')
                    ->columnSpan(2)
                    ->label(__('inspirecms::inspirecms.created_by'))
                    ->inlineLabel()
                    ->aboveContent(fn ($record) => $record->author?->name)
                    ->state(fn ($record) => $record->author?->email)
                    ->copyable()->copyableState(fn ($record) => $record->author?->email),

                Section::make()
                    ->heading(__('inspirecms::resources/export.tabs.details'))
                    ->collapsible()
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        TextEntry::make('display_exporter')
                            ->label(__('inspirecms::resources/export.exporter.label'))
                            ->inlineLabel(),

                        JsonEntry::make('message')
                            ->label(__('inspirecms::resources/export.message.label'))
                            ->state(function ($record) {
                                $payload = $record->payload;
                                if (! is_array($payload)) {
                                    return '';
                                }

                                return Arr::except($payload, ['result']);
                            }),

                        JsonEntry::make('payload.result')
                            ->label(__('inspirecms::resources/export.result.label')),
                    ]),
            ]);
    }
}
