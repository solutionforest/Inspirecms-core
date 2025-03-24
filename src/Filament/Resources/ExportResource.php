<?php

namespace SolutionForest\InspireCms\Filament\Resources;

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
use SolutionForest\InspireCms\Exports\Exporters\BaseExporter;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Helpers\UIHelper;
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
                        Infolists\Components\TextEntry::make('display_status')
                            ->inlineLabel()
                            ->label(__('inspirecms::resources/export.status.label'))
                            ->badge()
                            ->tooltip(function ($record) {
                                return $record->failed_at ?? $record->finished_at ?? null;
                            }),
                        Infolists\Components\TextEntry::make('file_name')
                            ->label(__('inspirecms::resources/export.result.label'))
                            ->inlineLabel()
                            ->fontFamily('mono')
                            ->suffixAction(function (Export | Model $record) {

                                [$fs, $path] = [$record->getDisk(), $record->file_name];

                                return Infolists\Components\Actions\Action::make('download')
                                    ->icon(FilamentIcon::resolve('inspirecms::download'))
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
                            ->label(__('inspirecms::resources/export.finished_at.label'))
                            ->inlineLabel(),
                        Infolists\Components\TextEntry::make('failed_at')
                            ->label(__('inspirecms::resources/export.failed_at.label'))
                            ->inlineLabel(),
                    ]),

                Infolists\Components\TextEntry::make('created_by')
                    ->columnSpan(2)
                    ->label(__('inspirecms::inspirecms.created_by'))
                    ->inlineLabel()
                    ->getStateUsing(fn ($record) => UIHelper::generateTextWithDescription(
                        text: $record->author?->name,
                        description: UIHelper::generateTextWithIcon(text: $record->author?->email, icon: FilamentIcon::resolve('inspirecms::email'))->toHtml()
                    ))
                    ->copyable()->copyableState(fn ($record) => $record->author?->email),

                Infolists\Components\Section::make()
                    ->heading(__('inspirecms::resources/export.tabs.details'))
                    ->collapsible()
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        Infolists\Components\TextEntry::make('display_exporter')
                            ->label(__('inspirecms::resources/export.exporter.label'))
                            ->inlineLabel(),

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
        $exporters = collect(InspireCmsConfig::get('exports.exporters', []))
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
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon(FilamentIcon::resolve('inspirecms::download'))
            ->emptyStateHeading(__('inspirecms::resources/export.empty_state.heading'))
            ->emptyStateDescription(__('inspirecms::resources/export.empty_state.description'))
            ->modelLabel(fn () => static::getModelLabel())
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id')),
                Tables\Columns\TextColumn::make('display_exporter')
                    ->label(__('inspirecms::resources/export.exporter.label')),
                Tables\Columns\TextColumn::make('display_status')
                    ->label(__('inspirecms::resources/export.status.label'))
                    ->badge()
                    ->tooltip(function ($record) {
                        return $record->failed_at ?? $record->finished_at ?? null;
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('inspirecms::inspirecms.created_at'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_by')
                    ->label(__('inspirecms::inspirecms.created_by'))
                    ->getStateUsing(fn ($record) => $record->author?->email)
                    ->description(fn ($record) => $record->author?->name, 'above')
                    ->icon(FilamentIcon::resolve('inspirecms::email'))
                    ->copyable(),
            ])
            ->recordAction('view')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->createAnother(false)
                    ->modalWidth('5xl')
                    ->stickyModalHeader()->stickyModalHeader()
                    ->slideOver()
                    ->form(fn (Form $form) => static::form($form))
                    ->label(__('inspirecms::buttons.export.label'))
                    ->modalSubmitActionLabel(__('inspirecms::buttons.export.label'))
                    ->successNotificationTitle(__('inspirecms::resources/export.notification.place_queue_success.title'))
                    ->failureNotificationTitle(__('inspirecms::resources/export.notification.place_queue_failue.title'))
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
            ->where(function (Builder $query) {

                $currentUser = auth()->user();
                $isSuperAdmin = $currentUser != null && is_inspirecms_user($currentUser) && $currentUser->isSuperAdmin();
    
                return $query
                    ->when(!$isSuperAdmin, fn (\Illuminate\Database\Eloquent\Builder $q) => $q->whereMorphedTo('author', $currentUser));
            });
    }

    // region Global search
    public static function canGloballySearch(): bool
    {
        return false;
    }
    // endregion Global search
}
