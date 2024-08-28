<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SolutionForest\FilamentFieldGroup\Filament\Resources\FieldGroupResource as BaseResource;
use SolutionForest\InspireCms\Filament\Forms\Components\RevertOrderGroup;
use SolutionForest\InspireCms\Filament\Forms\Components\TimestampsGroup;
use SolutionForest\InspireCms\Filament\Resources\Settings\FieldGroupResource\Pages;
use SolutionForest\InspireCms\Filament\Resources\Settings\FieldGroupResource\RelationManagers;
use SolutionForest\InspireCms\Filament\Tables\Actions\CloneAction;
use SolutionForest\InspireCms\Filament\Tables\Actions\QuickEditAction;
use SolutionForest\InspireCms\Models\FieldGroup;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class FieldGroupResource extends BaseResource
{
    protected static ?int $navigationSort = -9;

    protected static ?string $navigationIcon = null;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([

                RevertOrderGroup::make([

                    Forms\Components\Section::make()
                        ->columns(1)
                        ->schema([
                            static::getTimestampsGroupedFormComponent(),
                        ])
                        ->visible(fn ($operation) => $operation == 'edit')
                        ->grow(false),

                    Forms\Components\Section::make()
                        ->columns(2)
                        ->schema([
                            static::getTitleFormComponent(),
                            static::getNameFormComponent(),
                            static::getActiveFormComponent(),
                        ])
                        ->grow(),
                ])->revertBreakPoint('lg'),
            ]);
    }

    public static function quickForm(Form $form): Form
    {
        return $form
            ->schema([
                static::getTitleFormComponent()->inlineLabel(),
                static::getNameFormComponent()->inlineLabel(),
                static::getActiveFormComponent(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->modifyQueryUsing(fn ($query) => $query->withCount('fields')->withCount('documentTypes'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament-field-group::filament-field-group.name'))
                    ->sortable()->width('1%')
                    ->badge(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('filament-field-group::filament-field-group.title')),
                Tables\Columns\TextColumn::make('fields_count')
                    ->label(__('filament-field-group::filament-field-group.fields'))
                    ->alignEnd()
                    ->width('5%'),
                // Always true
                // Tables\Columns\BooleanColumn::make('active')
                //     ->label(__('filament-field-group::filament-field-group.active'))
                //     ->width('1%'),
                Tables\Columns\TextColumn::make('document_types_count')
                    ->label(__('inspirecms::inspirecms.total_xxx_have_used', [
                        'name' => (string)str(__('inspirecms::inspirecms.document_type'))->pluralStudly()->lower(),
                    ]))
                    ->alignEnd()
                    ->width('5%'),

                // timestamps
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('inspirecms::inspirecms.created_at'))
                    ->sortable()
                    ->formatStateUsing(fn (?\Carbon\Carbon $state) => $state?->diffForHumans())
                    ->width('5%'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('inspirecms::inspirecms.last_updated_at'))
                    ->sortable()
                    ->formatStateUsing(fn (?\Carbon\Carbon $state) => $state?->diffForHumans())
                    ->width('5%'),
            ])
            // Sync action formats
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\ActionGroup::make([

                    QuickEditAction::make(),

                    CloneAction::make()
                        ->recordTitleAttribute('title')
                        ->replicateExcepts(['fields_count', 'document_types_count'])
                        ->fillForm(fn (Model | FieldGroup $record) => [
                            'name' => $record->name,
                        ])
                        ->form([
                            static::getNameFormComponent()->autofocus(),
                        ])
                        ->saveRelationshipsUsing(function (Model | FieldGroup $originalRecord, Model | FieldGroup $record) {

                            $fields = $originalRecord->fields->map(fn (Model $field) => $field->replicate([
                                'group_id',
                            ])->toArray())->all();

                            $record->fields()->createMany($fields);
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->iconButton(),
            ])
            // Avoid delete
            ->checkIfRecordIsSelectableUsing(
                fn (Model | FieldGroup $record): bool => static::canDelete($record),
            );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageFieldGroup::route('/'),
            'edit' => Pages\EditFieldGroup::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make(fn () => __('inspirecms::inspirecms.details'), [

                RelationManagers\FieldsRelationManager::class,
                
            ])->icon('heroicon-m-adjustments-horizontal'),
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getFieldGroupModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.field_group');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('inspirecms::inspirecms.setting');
    }

    public static function getRecordSubTitle(?Model $record): string|Htmlable|null
    {
        return $record?->name ?? null;
    }

    public static function canDelete(Model $record): bool
    {
        return parent::canCreate($record) && ! $record->documentTypes()->exists();
    }

    //region Form field(s)/component(s)

    protected static function getTitleFormComponent(): Forms\Components\Component
    {
        return Forms\Components\TextInput::make('title')
            ->label(__('inspirecms::inspirecms.title'))
            ->required()
            ->maxLength(255)
            ->live(debounce: 500)
            ->afterStateUpdated(function ($operation, $state, Forms\Get $get, Forms\Set $set) {
                // Fill slug if empty / operation is create
                if ($operation === 'create' || empty($get('name'))) {
                    $set('name', Str::slug($state, '_'));
                }
            })
            ->autofocus();
    }

    protected static function getNameFormComponent(): Forms\Components\Component
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('inspirecms::inspirecms.name'))
            ->required()
            ->maxLength(255)
            ->live(debounce: 500)
            ->afterStateUpdated(fn ($component, ?string $state) => $component->state(Str::slug($state, '_')))
            ->unique(ignoreRecord: true);
    }

    protected static function getActiveFormComponent(): Forms\Components\Component
    {
        return Forms\Components\Hidden::make('active')
            ->dehydratedWhenHidden(true)
            ->dehydrateStateUsing(fn () => true);
    }

    protected static function getTimestampsGroupedFormComponent(): Forms\Components\Component
    {
        return TimestampsGroup::make()
            ->columns(['default' => 1]);
    }

    //endregion Form field(s)/component(s)
}
