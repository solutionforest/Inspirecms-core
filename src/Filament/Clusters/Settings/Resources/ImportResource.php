<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Base\Enums\ImportStatus;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\ImportResource\Pages;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Helpers\ImportDataHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Import;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class ImportResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?int $navigationSort = -3;

    protected static ?string $navigationIcon = 'heroicon-c-arrow-up-tray';

    protected static ?string $cluster = Settings::class;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'view',
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
                            ->label(__('inspirecms::resources/import.status.label'))
                            ->inlineLabel()
                            ->badge()
                            ->iconColor(function ($state) {
                                if ($state instanceof ImportStatus) {
                                    return $state->getColor();
                                }

                                return null;
                            }),
                        Infolists\Components\TextEntry::make('file_name')
                            ->label(__('inspirecms::resources/import.file_name.label'))
                            ->inlineLabel()
                            ->fontFamily('mono')
                            ->suffixAction(function (Import | Model $record) {

                                [$fs, $path] = $record->getStorageAndFilePath();

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
                        Infolists\Components\TextEntry::make('available_at')
                            ->label(__('inspirecms::resources/import.available_at.label'))
                            ->inlineLabel()
                            ->since()
                            ->dateTimeTooltip(),
                        Infolists\Components\TextEntry::make('finished_at')
                            ->label(__('inspirecms::resources/import.finished_at.label'))
                            ->inlineLabel(),
                        Infolists\Components\TextEntry::make('failed_at')
                            ->label(__('inspirecms::resources/import.failed_at.label'))
                            ->inlineLabel(),
                        Infolists\Components\TextEntry::make('clear_at')
                            ->weight('bold')
                            ->label(__('inspirecms::resources/import.clear_at.label'))
                            ->inlineLabel()
                            ->since()
                            ->dateTimeTooltip(),
                    ]),

                \SolutionForest\InspireCms\Filament\Infolists\Components\JsonEntry::make('payload')
                    ->label(__('inspirecms::resources/import.payload.label'))
                    ->columnSpanFull()
                    ->darkTheme('tomorrow_night_eighties'),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Hidden::make('file_disk'),
                Forms\Components\DateTimePicker::make('available_at')
                    ->label(__('inspirecms::resources/import.available_at.label'))
                    ->validationAttribute(__('inspirecms::resources/import.available_at.validation_attribute'))
                    ->helperText(__('inspirecms::resources/import.available_at.instructions'))
                    ->hint(__('inspirecms::resources/import.available_at.hint'))
                    ->native(false)
                    ->autofocus(false),
                Forms\Components\FileUpload::make('file_name')
                    ->required()
                    ->label(__('inspirecms::resources/import.file_name.label'))
                    ->validationAttribute(__('inspirecms::resources/import.file_name.validation_attribute'))
                    ->hint(__('inspirecms::resources/import.file_name.hint'))
                    ->disk(ImportDataHelper::getDiskDriver())
                    ->acceptedFileTypes([
                        //zip
                        ...[
                            'application/zip',
                            'application/octet-stream',
                            'application/x-zip-compressed',
                            'multipart/x-zip',
                        ],
                    ])
                    ->preserveFilenames(false),

                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('download_sample')
                        ->label(__('inspirecms::resources/import.actions.download_sample.label'))
                        ->icon('heroicon-s-arrow-down-on-square')
                        ->button()
                        ->outlined()
                        ->color('warning')
                        ->url(function () {
                            try {
                                return route('cms.import.sample');
                            } catch (RouteNotFoundException $th) {
                                return null;
                            }
                        }),
                ])->alignEnd(),
                Forms\Components\Placeholder::make('file_structure_instructions')
                    ->label(__('inspirecms::resources/import.file_structure_instructions.label'))
                    ->hint(__('inspirecms::resources/import.file_structure_instructions.hint'))
                    ->hintColor('warning')
                    ->content(view('inspirecms::import.file-structure-sample', [
                        'structure' => ImportDataHelper::getSampleFileStructure(),
                    ]))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon('heroicon-o-arrow-up-on-square')
            ->emptyStateHeading(__('inspirecms::resources/import.empty_state.heading'))
            ->emptyStateDescription(__('inspirecms::resources/import.empty_state.description'))
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id')),
                Tables\Columns\TextColumn::make('file_name')
                    ->label(__('inspirecms::resources/import.file_name.label'))
                    ->fontFamily('mono'),
                Tables\Columns\TextColumn::make('display_status')
                    ->label(__('inspirecms::resources/import.status.label'))
                    ->badge()
                    ->iconColor(function ($state) {
                        if ($state instanceof ImportStatus) {
                            return $state->getColor();
                        }

                        return null;
                    }),
                Tables\Columns\TextColumn::make('available_at')
                    ->label(__('inspirecms::resources/import.available_at.label'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('inspirecms::inspirecms.created_at'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('clear_at')
                    ->label(__('inspirecms::resources/import.clear_at.label'))
                    ->formatStateUsing(fn (?\Carbon\Carbon $state) => $state?->diffForHumans()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton()->slideOver(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImports::route('/'),
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getImportModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.import');
    }

    //region Global search
    public static function canGloballySearch(): bool
    {
        return false;
    }
    //endregion Global search
}
