<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Exports\Exporters\BaseExporter;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
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

    protected static bool $shouldRegisterNavigation = false;

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

                Infolists\Components\Section::make()
                    ->heading('Detail')
                    ->collapsible()
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        Infolists\Components\TextEntry::make('exporter')
                            ->label(__('inspirecms::resources/export.exporter.label'))
                            ->inlineLabel()
                            ->formatStateUsing(function ($state) {
                                if (is_string($state) && class_exists($state)) {
                                    return $state::getLabel();
                                }

                                return $state;
                            }),

                        \SolutionForest\InspireCms\Filament\Infolists\Components\JsonEntry::make('payload')
                            ->label(__('inspirecms::resources/export.message.label'))
                            ->columnSpanFull()
                            ->darkTheme('tomorrow_night_eighties')
                            ->getStateUsing(function ($record) {
                                $payload = $record->payload;

                                return collect($payload)->except('result')->all();
                            }),

                        \SolutionForest\InspireCms\Filament\Infolists\Components\JsonEntry::make('payload.result')
                            ->label(__('inspirecms::resources/export.result.label'))
                            ->columnSpanFull()
                            ->darkTheme('tomorrow_night_eighties'),
                    ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        $exporters = collect([
            \SolutionForest\InspireCms\Exports\Exporters\DocumentTypeExporter::class,
            \SolutionForest\InspireCms\Exports\Exporters\FieldGroupExporter::class,
            \SolutionForest\InspireCms\Exports\Exporters\TemplateExporter::class,
        ])
            ->mapWithKeys(fn ($exporter) => [$exporter => $exporter::getLabel()])
            ->all();

        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Select::make('exporter')
                    ->options($exporters)
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Components\Select $component) => $component
                        ->getContainer()
                        ->getComponent('dynamicTypeFields')
                        ->getChildComponentContainer()
                        ->fill()),

                Forms\Components\Group::make()
                    ->statePath('payload.args')
                    ->key('dynamicTypeFields')
                    ->dehydrated(true)
                    ->schema(function (Forms\Get $get) {

                        $exporter = $get('exporter');

                        if ($exporter && is_string($exporter) && class_exists($exporter) && is_a($exporter, BaseExporter::class, true)) {
                            $fields = $exporter::getArgsFormFields();

                            return $fields;
                        }

                        return [];
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        // todo: add translations
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon(FilamentIcon::resolve('inspirecms::download'))
            ->emptyStateHeading(__('inspirecms::resources/export.empty_state.heading'))
            ->emptyStateDescription(__('inspirecms::resources/export.empty_state.description'))
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
            ->recordAction('view')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->createAnother(false)
                    ->modalWidth('lg')
                    ->stickyModalHeader()->stickyModalHeader()
                    ->slideOver()
                    ->form(fn (Form $form) => static::form($form))
                    // todo: add translations
                    ->label('Add')
                    ->modalSubmitActionLabel('Export')
                    ->successNotificationTitle('Queued for export, please wait for the download link.')
                    ->failureNotificationTitle('Missing required data, failed to export.')
                    ->failureNotification(fn (Notification $notification) => $notification->warning())
                    ->using(function (Tables\Actions\CreateAction $action, array $data, string $model) {

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
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton()->slideOver()->authorize(null),
            ]);
    }

    public static function getPages(): array
    {
        return [];
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

                if ($currentUser != null && (is_inspirecms_user($currentUser) && ! $currentUser->isSuperAdmin())) {
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
