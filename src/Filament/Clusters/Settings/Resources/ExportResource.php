<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\ExportResource\Pages;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Export;

class ExportResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?int $navigationSort = -2;

    protected static ?string $navigationIcon = 'heroicon-c-arrow-down-tray';

    protected static ?string $cluster = Settings::class;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'create',
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(3)
            ->schema([
                Infolists\Components\Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label(__('inspirecms::inspirecms.id'))
                            ->inlineLabel(),
                        Infolists\Components\TextEntry::make('status')
                            ->inlineLabel()
                            ->badge()
                            ->getStateUsing(function ($record) {
                                [$failed, $finished] = [$record->failed_at, $record->finished_at];
                                if ($failed !== null) {
                                    return 'failed';
                                } elseif ($finished !== null) {
                                    return 'finished';
                                } else {
                                    return 'pending';
                                }
                            })
                            ->formatStateUsing(fn ($state) => Str::title($state))
                            ->tooltip(function ($record) {
                                return $record->failed_at ?? $record->finished_at ?? null;
                            })
                            ->color(function ($state) {
                                return match ($state) {
                                    'failed' => 'danger',
                                    'finished' => 'success',
                                    default => 'gray',
                                };
                            }),
                        Infolists\Components\TextEntry::make('file_name')
                            ->label('File')
                            ->inlineLabel()
                            ->fontFamily('mono')
                            ->suffixAction(function (Export | Model $record) {

                                [$fs, $path] = [$record->getDisk(), $record->file_name];

                                return Infolists\Components\Actions\Action::make('download')
                                    ->icon('heroicon-s-arrow-down-on-square')
                                    ->color('info')
                                    ->extraAttributes(['aria-label' => 'download'])
                                    ->action(fn () => $fs->download($path));
                            }),
                    ]),

                Infolists\Components\Group::make()
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label(__('inspirecms::inspirecms.created_at'))
                            ->inlineLabel()
                            ->since()
                            ->dateTimeTooltip(),
                        Infolists\Components\TextEntry::make('finished_at')
                            ->inlineLabel(),
                        Infolists\Components\TextEntry::make('failed_at')
                            ->inlineLabel(),
                    ]),

                Infolists\Components\Group::make()
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        Infolists\Components\TextEntry::make('author.name')->inlineLabel()->copyable(),
                        Infolists\Components\TextEntry::make('author.email')->inlineLabel()->copyable(),
                    ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\ToggleButtons::make('exporter')
                    ->options(
                        collect([
                            \SolutionForest\InspireCms\Exporters\DocumentTypeExporter::class,
                            \SolutionForest\InspireCms\Exporters\FieldGroupExporter::class,
                        ])
                            ->mapWithKeys(fn ($exporter) => [$exporter => class_basename($exporter)])
                            ->all()
                    )
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        //todo: add translations
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon('heroicon-o-arrow-down-on-square')
            // ->emptyStateHeading(__('inspirecms::resources/import.empty_state.heading'))
            // ->emptyStateDescription(__('inspirecms::resources/import.empty_state.description'))
            ->modifyQueryUsing(function ($query) {
                $currentUser = auth()->user();
                $isSuperAdmin = $currentUser != null && is_inspirecms_user($currentUser) && $currentUser->isSuperAdmin();
                return $query
                    ->with([
                        'author' => fn ($userQ) => $userQ
                            ->when(! $isSuperAdmin, fn ($q) => $q->whereKey($currentUser?->getKey())),
                    ]);
            })
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id')),
                // todo: add enum + attribute
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        [$failed, $finished] = [$record->failed_at, $record->finished_at];
                        if ($failed !== null) {
                            return 'failed';
                        } elseif ($finished !== null) {
                            return 'finished';
                        } else {
                            return 'pending';
                        }
                    })
                    ->formatStateUsing(fn ($state) => Str::title($state))
                    ->tooltip(function ($record) {
                        return $record->failed_at ?? $record->finished_at ?? null;
                    })
                    ->color(function ($state) {
                        return match ($state) {
                            'failed' => 'danger',
                            'finished' => 'success',
                            default => 'gray',
                        };
                    }),
                Tables\Columns\TextColumn::make('exporter')
                    ->formatStateUsing(fn ($state) => class_basename($state)),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('inspirecms::inspirecms.created_at'))
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton()->slideOver()->authorize(null),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExports::route('/'),
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getExportModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.export');
    }
    

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'author',
            ])
            ->whereHas('author', function (Builder $query) {
                
                $currentUser = auth()->user();

                if ($currentUser != null && (is_inspirecms_user($currentUser) && !$currentUser->isSuperAdmin())) {
                    return $query->whereKey($currentUser->getKey());
                }
                    
                return $query;
            });
    }

    // region Global search
    public static function canGloballySearch(): bool
    {
        return false;
    }
    // endregion Global search
}
