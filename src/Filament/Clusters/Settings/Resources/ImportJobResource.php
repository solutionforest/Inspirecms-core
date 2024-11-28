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
use Parallax\FilamentSyntaxEntry\SyntaxEntry;
use SolutionForest\InspireCms\Base\Enums\ImportJobStatus;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\ImportJobResource\Pages;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\ImportJob;
use SolutionForest\InspireCms\Services\ImportJobServiceInterface;

class ImportJobResource extends Resource implements ClusterSectionResource
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
            ->schema([
                Infolists\Components\Group::make()
                    ->schema([
                        Infolists\Components\TextEntry::make('display_status')
                            ->label(__('inspirecms::inspirecms.status'))
                            ->badge()
                            ->iconColor(function ($state) {
                                if ($state instanceof ImportJobStatus) {
                                    return $state->getColor();
                                }

                                return null;
                            }),
                        Infolists\Components\TextEntry::make('file')
                            ->label(__('inspirecms::resources/import-jobs.file.title'))
                            ->fontFamily('mono')
                            ->suffixAction(function (ImportJob & Model $record) {

                                [$fs, $path] = $record->getStorageAndFilePath();

                                return Infolists\Components\Actions\Action::make('download')
                                    ->icon('heroicon-o-arrow-down-on-square')
                                    ->action(fn () => $fs->download($path));
                            }),
                    ]),

                Infolists\Components\Group::make()
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label(__('inspirecms::inspirecms.created_at'))
                            ->since()
                            ->dateTimeTooltip(),
                        Infolists\Components\TextEntry::make('available_at')
                            ->label(__('inspirecms::resources/import-jobs.available_at.title'))
                            ->since()
                            ->dateTimeTooltip(),
                        Infolists\Components\TextEntry::make('finished_at')
                            ->label(__('inspirecms::resources/import-jobs.finished_at.title')),
                        Infolists\Components\TextEntry::make('failed_at')
                            ->label(__('inspirecms::resources/import-jobs.failed_at.title')),
                        Infolists\Components\TextEntry::make('clear_at')
                            ->weight('bold')
                            ->label(__('inspirecms::resources/import-jobs.clear_at.title'))
                            ->since()
                            ->dateTimeTooltip(),
                    ]),

                SyntaxEntry::make('payload')
                    ->label(__('inspirecms::resources/import-jobs.payload.title'))
                    ->columnSpanFull()
                    ->language('json'),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Hidden::make('disk'),
                Forms\Components\FileUpload::make('file')
                    ->required()
                    ->label(__('inspirecms::resources/import-jobs.file.title'))
                    ->hint(__('inspirecms::resources/import-jobs.file.instructions'))
                    ->helperText(app(ImportJobServiceInterface::class)->getFileStructureHtml())
                    ->disk(app(ImportJob::class)->getDiskDriver())
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon('heroicon-o-arrow-up-on-square')
            ->emptyStateHeading(__('inspirecms::resources/import-jobs.empty.title'))
            ->emptyStateDescription(__('inspirecms::resources/import-jobs.empty.description'))
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id')),
                Tables\Columns\TextColumn::make('file')
                    ->label(__('inspirecms::resources/import-jobs.file.title'))
                    ->fontFamily('mono'),
                Tables\Columns\TextColumn::make('display_status')
                    ->badge()
                    ->label(__('inspirecms::inspirecms.status'))
                    ->iconColor(function ($state) {
                        if ($state instanceof ImportJobStatus) {
                            return $state->getColor();
                        }

                        return null;
                    }),
                Tables\Columns\TextColumn::make('available_at')
                    ->label(__('inspirecms::resources/import-jobs.available_at.title'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('inspirecms::inspirecms.created_at'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('clear_at')
                    ->label(__('inspirecms::resources/import-jobs.clear_at.title'))
                    ->formatStateUsing(fn (?\Carbon\Carbon $state) => $state?->diffForHumans()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImportJobs::route('/'),
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getImportJobModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.import_jobs');
    }

    //region Global search
    public static function canGloballySearch(): bool
    {
        return false;
    }
    //endregion Global search
}
