<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use SolutionForest\FilamentFieldGroup\Facades\FilamentFieldGroup;
use SolutionForest\FilamentFieldGroup\Models\Contracts\Field;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldResource\Pages;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Forms\Components\BelongsToParentSelect;
use SolutionForest\InspireCms\InspireCmsConfig;

class FieldResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?int $navigationSort = -8;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $recordTitleAttribute = 'label';

    protected static ?string $cluster = Settings::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Hidden::make('id'),
                        Forms\Components\Hidden::make('sort'),
                        static::getLabelFormComponent(),
                        static::getNameFormComponent(),
                        static::getInstructionsFormComponent(),
                        static::getTypeFormComponent(),
                    ])
                    ->columnSpan(1),

                Forms\Components\Section::make()
                    ->schema([
                        static::getGroupFormComponent()->inlineLabel(),
                        static::getStatePathFormComponent()
                            ->hidden()->dehydrated(),
                        static::getMandatoryFormComponent()->columnSpanFull(),
                    ])
                    ->columnSpan(1),
                static::getConfigFormComponent()->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                Tables\Grouping\Group::make('type')
                    ->label(__('inspirecms::inspirecms.type'))
                    ->getTitleFromRecordUsing(fn (Field $record) => FilamentFieldGroup::getFieldTypeDisplayValue($record->type))
                    ->collapsible(),
                Tables\Grouping\Group::make('group_id')
                    ->label(__('inspirecms::inspirecms.group'))
                    ->getTitleFromRecordUsing(fn (Field $record) => $record->group?->title)
                    ->collapsible(),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label(__('inspirecms::inspirecms.label'))
                    ->sortable()
                    ->tooltip(fn (Field $record) => $record->name),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('inspirecms::inspirecms.type'))
                    ->sortable()
                    ->icon(fn ($state) => FilamentFieldGroup::getFieldTypeIcon($state))
                    ->formatStateUsing(fn ($state) => FilamentFieldGroup::getFieldTypeDisplayValue($state)),
                Tables\Columns\TextColumn::make('group.title')
                    ->label(__('inspirecms::inspirecms.group'))
                    ->tooltip(fn (Field $record) => $record->group?->name),

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
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
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
            'index' => Pages\ListFields::route('/'),
            'create' => Pages\CreateField::route('/create'),
            'edit' => Pages\EditField::route('/{record}/edit'),
            'view' => Pages\ViewField::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['group']);
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getFieldModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.field');
    }

    //region Global search
    public static function canGloballySearch(): bool
    {
        return false;
    }
    //endregion Global search

    //region Form field(s)/component(s)
    /** @return Forms\Components\Field|Forms\Components\Component */
    public static function getNameFormComponent()
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('inspirecms::resources/field.name.label'))
            ->inlineLabel()
            ->placeholder(__('inspirecms::resources/field.name.label'))
            ->helperText(__('inspirecms::resources/field.name.helper'))
            ->required()
            ->maxLength(255)
            ->live(debounce: 500)
            ->afterStateUpdated(fn ($component, ?string $state) => $component->state(Str::slug($state, '_')))
            ->unique(table: InspireCmsConfig::getFieldModelClass(), column: 'name', ignorable: function ($component, Forms\Get $get) {
                $id = $get('id');

                return InspireCmsConfig::getFieldModelClass()::find($id);
            }, modifyRuleUsing: function (Unique $rule, ?Model $record, $get) {
                $groupId = $record?->group_id ?? $get('group_id') ?? 0;

                return $rule
                    ->where('group_id', $groupId);
            });
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    public static function getLabelFormComponent()
    {
        return Forms\Components\TextInput::make('label')
            ->label(__('inspirecms::resources/field.label.label'))
            ->inlineLabel()
            ->placeholder(__('inspirecms::resources/field.label.label'))
            ->helperText(__('inspirecms::resources/field.label.helper'))
            ->required()
            ->columnSpan('full')
            ->maxLength(255)
            ->live(debounce: 500)
            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('name', Str::slug($state, '_')));
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    public static function getInstructionsFormComponent()
    {
        return Forms\Components\TextInput::make('instructions')
            ->label(__('inspirecms::resources/field.instructions.label'))
            ->inlineLabel()
            ->placeholder(__('inspirecms::resources/field.instructions.label'))
            ->helperText(__('inspirecms::resources/field.instructions.helper'))
            ->maxLength(255)
            ->columnSpan('full');
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    public static function getTypeFormComponent()
    {
        return Forms\Components\Select::make('type')
            ->label(__('inspirecms::resources/field.type.label'))
            ->inlineLabel()
            ->placeholder(__('inspirecms::resources/field.type.label'))
            ->helperText(__('inspirecms::resources/field.type.helper'))
            ->columns(4)
            ->options(FilamentFieldGroup::getFieldTypeGroupedKeyValueWithIconOptions())
            ->searchable()
            ->allowHtml()
            ->required()
            ->columnSpan('full')
            ->live(debounce: 500)
            ->afterStateUpdated(fn (Forms\Components\Select $component) => $component
                ->getContainer()
                ->getParentComponent()->getContainer() // section
                ->getComponent('configFields')
                ?->getChildComponentContainer()
                ?->fill());
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    public static function getStatePathFormComponent()
    {
        return Forms\Components\TextInput::make('state_path')
            ->label(__('inspirecms::resources/field.state_path.label'))
            ->inlineLabel()
            ->placeholder(__('inspirecms::resources/field.state_path.label'))
            ->helperText(__('inspirecms::resources/field.state_path.helper'))
            ->maxLength(255);
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    public static function getMandatoryFormComponent()
    {
        return Forms\Components\Toggle::make('mandatory')
            ->label(__('inspirecms::resources/field.mandatory.label'))
            ->inlineLabel()
            ->helperText(__('inspirecms::resources/field.mandatory.helper'))
            ->inlineLabel();
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    public static function getConfigFormComponent()
    {
        return Forms\Components\Group::make()
            ->key('configFields')
            ->statePath('config')
            ->schema(function (Forms\Get $get) {

                if ($type = $get('type')) {
                    return FilamentFieldGroup::getFieldTypeConfigFormSchema($type);
                }

                return [];
            });
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    public static function getGroupFormComponent()
    {
        return BelongsToParentSelect::make('group_id')
            ->label(__('inspirecms::resources/field.group.label'))
            ->nestableParentRelationship('group', 'title')
            ->searchable()
            ->preload();
    }
    //endregion Form field(s)/component(s)
}
