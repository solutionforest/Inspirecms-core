<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Forms\Components\DocumentFieldGroup;
use SolutionForest\InspireCms\Filament\Forms\Components\RevertOrderGroup;
use SolutionForest\InspireCms\Filament\Resources\Settings\DocumentTypeResource\Pages;
use SolutionForest\InspireCms\Filament\Tables\Actions\CloneAction;
use SolutionForest\InspireCms\Filament\Tables\Actions\QuickEditAction;
use SolutionForest\InspireCms\Models\CmsDocumentType;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class DocumentTypeResource extends Resource
{
    protected static ?int $navigationSort = -10;

    protected static ?string $navigationIcon = null;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                RevertOrderGroup::make([

                    Forms\Components\Section::make()
                        ->columns(1)
                        ->schema([
                            static::getCanUseAtRootFormComponent(),
                            static::getTimestampsGroupedFormComponent(),
                        ])
                        ->grow(false),
                    Forms\Components\Section::make()
                        ->columns(1)
                        ->schema([
                            static::getTitleFormComponent()->inlineLabel()->columnSpanFull(),
                            static::getFieldGroupFormComponent(),
                        ])
                        ->grow(),
                ]),
            ]);
    }

    public static function quickForm(Form $form): Form
    {
        return $form
            ->schema([
                static::getTitleFormComponent()->inlineLabel(),
                static::getCanUseAtRootFormComponent(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id'))
                    ->width('1%')->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::inspirecms.title'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('can_use_at_root')
                    ->label(__('inspirecms::inspirecms.can_use_at_root'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\ActionGroup::make([

                    QuickEditAction::make(),
                    
                    CloneAction::make()
                            ->recordTitleAttribute('title')
                        ->saveRelationshipsUsing(function (Model|CmsDocumentType $originalRecord, Model|CmsDocumentType $record) {

                            $fieldGroups = $originalRecord->morphFieldGroups->map(fn (Model $originalFieldGroup) => $originalFieldGroup->replicate([
                                'model_type',
                                'model_id'
                            ])->toArray())->all();

                            $record->morphFieldGroups()->createMany($fieldGroups);
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->iconButton(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumentTypes::route('/'),
            'create' => Pages\CreateDocumentType::route('/create'),
            'edit' => Pages\EditDocumentType::route('/{record}/edit'),
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getDocumentTypeModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.document_type');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('inspirecms::inspirecms.setting');
    }

    //region Form field(s)/component(s)

    protected static function getTitleFormComponent(): Forms\Components\Component
    {
        return Forms\Components\TextInput::make('title')
            ->label(__('inspirecms::inspirecms.title'))
            ->required();
    }

    protected static function getFieldGroupFormComponent(): Forms\Components\Component
    {
        return DocumentFieldGroup::make()->inlineLabel();
    }

    protected static function getCanUseAtRootFormComponent(): Forms\Components\Component
    {
        return Forms\Components\Toggle::make('can_use_at_root')
            ->label(__('inspirecms::inspirecms.can_use_at_root'))
            ->inlineLabel()
            ->default(false);
    }

    protected static function getTimestampsGroupedFormComponent(): Forms\Components\Component
    {
        return Forms\Components\Group::make([
            Forms\Components\Placeholder::make('created_at')
                ->content(fn ($record) => $record->created_at?->shortRelativeToNowDiffForHumans())
                ->label(__('inspirecms::inspirecms.created_at'))
                ->inlineLabel(),
            Forms\Components\Placeholder::make('updated_at')
                ->content(fn ($record) => $record->updated_at?->shortRelativeToNowDiffForHumans())
                ->label(__('inspirecms::inspirecms.last_updated_at'))
                ->inlineLabel(),
        ])->visible(fn ($operation) => $operation == 'edit')
            ->columns(['default' => 1]);
    }

    //endregion Form field(s)/component(s)
}
