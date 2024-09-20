<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use SolutionForest\FilamentFieldGroup\Facades\FilamentFieldGroup;
use SolutionForest\FilamentFieldGroup\Filament\Resources\FieldGroupResource as BaseResource;
use SolutionForest\FilamentFieldGroup\Models\Contracts\FieldGroup;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource\Pages;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource\RelationManagers;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Forms\Components\RevertOrderGroup;
use SolutionForest\InspireCms\Filament\Forms\Components\TimestampsGroup;
use SolutionForest\InspireCms\Filament\Tables\Actions\CloneAction;
use SolutionForest\InspireCms\Filament\Tables\Actions\QuickEditAction;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class FieldGroupResource extends BaseResource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?int $navigationSort = -9;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $cluster = Settings::class;

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

                Forms\Components\Section::make()
                    ->heading(__('inspirecms::inspirecms.fields'))
                    ->aside()
                    ->compact()
                    ->schema([
                        static::getFieldsFormComponent(),
                    ]),
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
            ->modifyQueryUsing(fn ($query) => $query->withCount(['fields', 'documentTypes']))
            ->emptyStateActions([])
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
                        'name' => (string) str(__('inspirecms::inspirecms.document_type'))->pluralStudly()->lower(),
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
                            'title' => $record->title,
                            'name' => $record->name,
                        ])
                        ->form([
                            static::getTitleFormComponent()->autofocus(false),
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
            RelationManagers\DocumentTypesRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                // Delete event checking
                'documentTypes',
            ]);
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getFieldGroupModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.field_group');
    }

    public static function getRecordSubTitle(?Model $record): string | Htmlable | null
    {
        return $record?->name ?? null;
    }

    public static function canDelete(Model $record): bool
    {
        if (! parent::canCreate($record)) {
            return false;
        }

        // Load docuemnt types if haven't loaded
        if (! $record->relationLoaded('documentTypes')) {
            $record->loadMissing('documentTypes');
        }

        return $record->documentTypes->count() <= 0;
    }

    //region Form field(s)/component(s)
    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTitleFormComponent()
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

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getNameFormComponent()
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('inspirecms::inspirecms.name'))
            ->required()
            ->maxLength(255)
            ->live(debounce: 500)
            ->afterStateUpdated(fn ($component, ?string $state) => $component->state(Str::slug($state, '_')))
            ->unique(
                table: static::getModel(),
                column: 'name',
                ignoreRecord: true
            );
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getActiveFormComponent()
    {
        return Forms\Components\Hidden::make('active')
            ->dehydratedWhenHidden(true)
            ->dehydrateStateUsing(fn () => true);
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTimestampsGroupedFormComponent()
    {
        return TimestampsGroup::make()
            ->columns(['default' => 1]);
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    protected static function getFieldsFormComponent()
    {
        return Forms\Components\Repeater::make('fields')
            ->key('fieldsRepeater')
            ->hiddenLabel()
            ->validationAttribute(strtolower(__('inspirecms::inspirecms.fields')))
            ->relationship('fields')
            ->itemLabel(fn (array $state): ?string => $state['label'] ?? $state['name'] ?? null)
            ->collapsible()->collapsed()
            ->orderColumn('sort')
            ->addActionLabel(fn () => __('inspirecms::inspirecms.add_xxx', ['name' => strtolower(__('inspirecms::inspirecms.fields'))]))
            ->addAction(
                fn (Forms\Components\Actions\Action $action) => $action
                    ->size(ActionSize::ExtraLarge)
                    ->extraAttributes(['class' => 'w-full'])
                    ->slideOver()
                    ->modalWidth('5xl')
                    ->form(static::getFieldsEditFormSchema())
                    ->action(function (array $data, Forms\Components\Repeater $component) {
                        $newUuid = $component->generateUuid();

                        $items = $component->getState();

                        if ($newUuid) {
                            $items[$newUuid] = $data;
                        } else {
                            $items[] = $data;
                        }

                        $component->state($items);

                        $component->getChildComponentContainer($newUuid ?? array_key_last($items))->fill($data);

                        $component->collapsed(false, shouldMakeComponentCollapsible: false);

                        $component->callAfterStateUpdated();
                    })
            )
            ->extraItemActions([
                Forms\Components\Actions\Action::make('edit')
                    ->icon(FilamentIcon::resolve('actions::edit-action') ?? 'heroicon-m-pencil-square')
                    ->label(__('filament-actions::edit.single.label'))
                    ->color('gray')
                    ->slideOver()
                    ->modalWidth('5xl')
                    ->fillForm(function (array $arguments, Forms\Components\Repeater $component) {

                        $itemData = $component->getRawItemState($arguments['item']);

                        $relationship = $component->getRelationship();

                        $existing = $component->getCachedExistingRecords()->get($arguments['item']);

                        if ($existing) {
                            $model = $existing;
                        } else {
                            $model = $relationship->getRelated()->fill($itemData);
                        }

                        return $model->attributesToArray();

                    })
                    ->form(function (Form $form, array $arguments, Forms\Components\Repeater $component) {
                        return $form
                            ->model($component->getRelationship()->getRelated())
                            ->schema(static::getFieldsEditFormSchema());
                    })
                    ->action(function (array $data, array $arguments, Forms\Components\Repeater $component) {
                        $uuid = $arguments['item'] ?? null;

                        $items = $component->getState();

                        if (filled($uuid) && isset($items[$uuid])) {
                            $items[$uuid] = $data;

                            $component->state($items);

                            $component->getChildComponentContainer($uuid)->fill($data);

                            $component->collapsed(false, shouldMakeComponentCollapsible: false);

                            $component->callAfterStateUpdated();
                        }
                    }),
            ])
            ->schema(static::getFieldsRepeaterSchema());
    }

    protected static function getFieldsRepeaterSchema(): array
    {
        return [
            Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\Hidden::make('id'),
                    Forms\Components\Hidden::make('group_id'),
                    Forms\Components\Hidden::make('sort'),
                    static::getFieldsTypeFormComponent()->helperText('')
                        ->disabled()->saveRelationshipsWhenDisabled()->dehydrated()
                        ->columnSpanFull(),
                    Forms\Components\Section::make(__('inspirecms::inspirecms.details'))
                        ->columnSpanFull()
                        ->aside()
                        ->schema([
                            static::getFieldsLabelFormComponent()->helperText('')
                                ->disabled()->saveRelationshipsWhenDisabled()->dehydrated(),
                            static::getFieldsNameFormComponent()->helperText('')
                                ->disabled()->saveRelationshipsWhenDisabled()->dehydrated(),
                            static::getFieldsStatePathFormComponent()->helperText('')
                                ->disabled()->saveRelationshipsWhenDisabled()->dehydrated(),
                        ]),
                    static::getFieldsInstructionsFormComponent()->helperText('')
                        ->disabled()->saveRelationshipsWhenDisabled()->dehydrated()
                        ->columnSpanFull(),
                    static::getFieldsMandatoryFormComponent()->hidden()
                        ->saveRelationshipsWhenHidden()->dehydratedWhenHidden(),
                    Forms\Components\Hidden::make('config'),
                ]),
        ];
    }

    protected static function getFieldsEditFormSchema(): array
    {
        return [
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Hidden::make('id'),
                    Forms\Components\Hidden::make('group_id'),
                    Forms\Components\Hidden::make('sort'),
                    static::getFieldsLabelFormComponent(),
                    static::getFieldsNameFormComponent(),
                    static::getFieldsInstructionsFormComponent(),
                    static::getFieldsTypeFormComponent(),
                ]),

            Forms\Components\Section::make()
                ->schema([
                    static::getFieldsStatePathFormComponent(),
                    static::getFieldsMandatoryFormComponent(),
                ]),
            Forms\Components\Group::make()
                ->key('configFields')
                ->statePath('config')
                ->schema(function (Forms\Get $get) {

                    if ($get('type')) {
                        return FilamentFieldGroup::getFieldTypeConfigFormSchema($get('type'));
                    }

                    return [];
                }),
        ];
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    protected static function getFieldsNameFormComponent()
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('filament-field-group::filament-field-group.forms.fields.name.label'))
            ->inlineLabel()
            ->placeholder(__('filament-field-group::filament-field-group.forms.fields.name.label'))
            ->helperText(__('filament-field-group::filament-field-group.forms.fields.name.helper'))
            ->required()
            ->maxLength(255)
            ->live(debounce: 500)
            ->afterStateUpdated(fn ($component, ?string $state) => $component->state(Str::slug($state, '_')))
            ->unique(table: InspireCmsConfig::getFieldModelClass(), column: 'name', ignorable: function ($component, Forms\Get $get) {
                $id = $get('id');

                return InspireCmsConfig::getFieldModelClass()::find($id);
            }, modifyRuleUsing: function (Unique $rule, ?Model $record) {
                return $rule
                    ->where('group_id', $record?->getKey() ?? 0);
            });
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    protected static function getFieldsLabelFormComponent()
    {
        return Forms\Components\TextInput::make('label')
            ->label(__('filament-field-group::filament-field-group.forms.fields.label.label'))
            ->inlineLabel()
            ->placeholder(__('filament-field-group::filament-field-group.forms.fields.label.label'))
            ->helperText(__('filament-field-group::filament-field-group.forms.fields.label.helper'))
            ->required()
            ->columnSpan('full')
            ->maxLength(255)
            ->live(debounce: 500)
            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('name', Str::slug($state, '_')));
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    protected static function getFieldsInstructionsFormComponent()
    {
        return Forms\Components\TextInput::make('instructions')
            ->label(__('filament-field-group::filament-field-group.forms.fields.instructions.label'))
            ->inlineLabel()
            ->placeholder(__('filament-field-group::filament-field-group.forms.fields.instructions.label'))
            ->helperText(__('filament-field-group::filament-field-group.forms.fields.instructions.helper'))
            ->maxLength(255)
            ->columnSpan('full');
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    protected static function getFieldsTypeFormComponent()
    {
        return Forms\Components\Select::make('type')
            ->label(__('filament-field-group::filament-field-group.forms.fields.type.label'))
            ->inlineLabel()
            ->placeholder(__('filament-field-group::filament-field-group.forms.fields.type.label'))
            ->helperText(__('filament-field-group::filament-field-group.forms.fields.type.helper'))
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
    protected static function getFieldsStatePathFormComponent()
    {
        return Forms\Components\TextInput::make('state_path')
            ->label(__('filament-field-group::filament-field-group.forms.fields.state_path.label'))
            ->inlineLabel()
            ->placeholder(__('filament-field-group::filament-field-group.forms.fields.state_path.label'))
            ->helperText(__('filament-field-group::filament-field-group.forms.fields.state_path.helper'))
            ->maxLength(255);
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    protected static function getFieldsMandatoryFormComponent()
    {
        return Forms\Components\Toggle::make('mandatory')
            ->label(__('filament-field-group::filament-field-group.forms.fields.mandatory.label'))
            ->inlineLabel()
            ->helperText(__('filament-field-group::filament-field-group.forms.fields.mandatory.helper'))
            ->columnSpan('full')
            ->inlineLabel();
    }
    //endregion Form field(s)/component(s)
}
