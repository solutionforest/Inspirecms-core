<?php

namespace SolutionForest\InspireCms\Filament\Resources\Imports\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use SolutionForest\InspireCms\Base\Enums\ImportStatus;
use SolutionForest\InspireCms\Filament\Infolists\Components\JsonEntry;
use SolutionForest\InspireCms\Filament\Resources\Imports\Actions\DownloadImportAction;

class ImportInfolist
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
                            ->label(__('inspirecms::resources/import.status.label'))
                            ->inlineLabel()
                            ->badge()
                            ->iconColor(function ($state) {
                                if ($state instanceof ImportStatus) {
                                    return $state->getColor();
                                }

                                return null;
                            }),
                        TextEntry::make('file_name')
                            ->label(__('inspirecms::resources/import.file_name.label'))
                            ->inlineLabel()
                            ->fontFamily('mono')
                            ->afterContent(DownloadImportAction::make()),
                    ]),

                Group::make()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('inspirecms::inspirecms.created_at'))
                            ->inlineLabel()
                            ->since()
                            ->dateTimeTooltip(),
                        TextEntry::make('available_at')
                            ->label(__('inspirecms::resources/import.available_at.label'))
                            ->inlineLabel()
                            ->since()
                            ->dateTimeTooltip(),
                        TextEntry::make('finished_at')
                            ->label(__('inspirecms::resources/import.finished_at.label'))
                            ->inlineLabel(),
                        TextEntry::make('failed_at')
                            ->label(__('inspirecms::resources/import.failed_at.label'))
                            ->inlineLabel(),
                        TextEntry::make('clear_at')
                            ->weight('bold')
                            ->label(__('inspirecms::resources/import.clear_at.label'))
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

                JsonEntry::make('payload')
                    ->columnSpanFull()
                    ->label(__('inspirecms::resources/import.payload.label')),
            ]);
    }
}
