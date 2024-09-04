<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Contents\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Filament\Clusters\Contents;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource\Contracts\HasPublishForm;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource\Pages;
use SolutionForest\InspireCms\Filament\Forms\Components\Actions\ResetAction;
use SolutionForest\InspireCms\Filament\Forms\Components\BelongsToParentSelect;
use SolutionForest\InspireCms\Filament\Forms\Components\PropertyDataGroup;
use SolutionForest\InspireCms\Filament\Forms\Components\RevertOrderGroup;
use SolutionForest\InspireCms\Filament\Forms\Components\TimestampsGroup;
use SolutionForest\InspireCms\Models\Contracts\Content as CmsContent;
use SolutionForest\InspireCms\Models\Contracts\PropertyData as CmsPropertyData;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class PageResource extends Resource
{
    protected static ?int $navigationSort = -9;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $cluster = Contents::class;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                RevertOrderGroup::make([

                    Forms\Components\Group::make([

                        Forms\Components\Section::make()
                            ->columns(1)
                            ->schema([
                                static::getSlugFormComponent(),
                                static::getParentPageFormComponent(),
                            ]),
                        Forms\Components\Group::make()
                            ->columns(['default' => 1, 'lg' => 1, 'md' => 2])
                            ->visibleOn(['edit', 'view'])
                            ->schema([
                                static::getTimestampsGroupedFormComponent()->columnSpan(1),
                                static::getPublishDetailGroupedFormComponent()->columnSpan(1),
                            ]),

                    ])->grow(false),

                    Forms\Components\Group::make()
                        ->schema([

                            Forms\Components\Section::make()
                                ->columnSpanFull()
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->columnSpanFull()
                                        ->schema([
                                            static::getTitleFormComponent(),
                                        ]),
                                    Forms\Components\Grid::make(['default' => 4])
                                        ->columnSpanFull()
                                        ->schema([

                                            static::documentTypeSelectComponent()->columnSpan(3),
                                            static::getDisplayIsRootLevelFormComponent()->columnSpan(1),
                                        ]),
                                ]),

                            // Field group grouped component
                            static::getPropertyDataValueComponent(),
                        ])
                        ->grow(),
                ])->revertBreakPoint('lg'),
            ]);
    }

    public static function publishForm(Form $form): Form
    {
        return $form
            ->schema([
                static::getPublishedAtComponent(),
                Forms\Components\Group::make()
                    ->statePath('formData')
                    // Here can validate form data
                    ->afterStateHydrated(fn (HasPublishForm $livewire, $component) => $component->state($livewire->getPublishableFormDataBeforePublish([]))),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id'))
                    ->width('1%')->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::inspirecms.title'))
                    ->sortable()
                    ->grow(),
                Tables\Columns\IconColumn::make('documentType.can_use_at_root')
                    ->label(__('inspirecms::inspirecms.is_root_level'))
                    ->width('1%')
                    ->boolean()
                    ->alignCenter()->verticallyAlignCenter(),

                Tables\Columns\ColumnGroup::make(__('inspirecms::inspirecms.visibility'))
                    ->columns([

                        Tables\Columns\TextColumn::make('displayStatus')
                            ->label(__('inspirecms::inspirecms.status'))
                            ->formatStateUsing(fn (?ContentStatusOption $state) => $state->getLabel())
                            ->color(fn (?ContentStatusOption $state) => $state->getColor())
                            ->icon(fn (?ContentStatusOption $state) => $state->getIcon())
                            ->badge()
                            ->iconPosition(IconPosition::Before)
                            ->width('2%'),

                        Tables\Columns\IconColumn::make('is_published')
                            ->label(__('inspirecms::inspirecms.is_published'))
                            ->getStateUsing(fn (Model | CmsContent $record) => $record->isPublished())  // Already include private
                            ->boolean()
                            ->width('2%')
                            ->trueIcon('heroicon-m-eye')
                            ->falseIcon('heroicon-o-eye-slash')
                            ->falseColor('gray')
                            ->alignCenter()->verticallyAlignCenter(),

                        Tables\Columns\TextColumn::make('published_at')
                            ->label(__('inspirecms::inspirecms.publish_at'))
                            ->formatStateUsing(fn (?\Carbon\Carbon $state) => $state?->diffForHumans())
                            ->width('5%'),
                    ]),

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
                Tables\Actions\ViewAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->iconButton(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label(__('inspirecms::inspirecms.is_published'))
                    ->queries(
                        true: fn (Builder $query) => $query->isPublished(condition: true),
                        false: fn (Builder $query) => $query->isPublished(condition: false),
                        blank: fn (Builder $query) => $query,
                    ),
                Tables\Filters\TernaryFilter::make('is_root_level')
                    ->label(__('inspirecms::inspirecms.is_root_level'))
                    ->queries(
                        true: fn (Builder $query) => $query->isRootLevel(condition: true),
                        false: fn (Builder $query) => $query->isRootLevel(condition: false),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->recordUrl(function (Model $record, Table $table): ?string {
                // Revert action's order
                foreach (['edit', 'view'] as $action) { // foreach (['view', 'edit'] as $action) {
                    if (! static::hasPage($action)) {
                        continue;
                    }

                    if (! static::{'can' . ucfirst($action)}($record)) {
                        continue;
                    }

                    return static::getUrl($action, ['record' => $record]);
                }

                return null;
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
            'view' => Pages\ViewPage::route('/{record}'),
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getContentModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.page');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'propertyDatas', // To get latest version
            'documentType', // Determine the page "Is Root Level"
        ]);
    }

    //region Form field(s)/component(s)
    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTitleFormComponent()
    {
        return Forms\Components\TextInput::make('title')
            ->label(__('inspirecms::inspirecms.title'))
            ->validationAttribute(Str::lower(__('inspirecms::inspirecms.title')))
            ->live(debounce: 300)->afterStateUpdated(function ($state, $get, $set, $operation) {
                // Fill slug if empty / operation is create
                if ($operation === 'create' || empty($get('slug'))) {
                    $set('slug', Str::slug($state));
                }
            })
            ->autofocus()
            ->required();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getSlugFormComponent()
    {
        return Forms\Components\TextInput::make('slug')
            ->label(__('inspirecms::inspirecms.slug'))
            ->validationAttribute(Str::lower(__('inspirecms::inspirecms.slug')))
            ->live(debounce: 300)->afterStateUpdated(fn ($component, $state) => $component->state(Str::slug($state)))
            ->unique(column: 'slug', ignoreRecord: true, modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule, callable $get) {
                return $rule->where('parent_id', $get('parent_id') ?? 0);
            })
            ->required();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getPublishedAtComponent()
    {
        return Forms\Components\DateTimePicker::make('published_at')
            ->label(__('inspirecms::inspirecms.publish_at'))
            ->native(false)
            ->prefixIcon('heroicon-m-calendar-date-range')
            ->suffixAction(ResetAction::make())
            ->hintIcon(
                'heroicon-o-information-circle',
                __('inspirecms::inspirecms.hints.future_publish')
            )
            ->default(now())
            ->required();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getParentPageFormComponent()
    {
        return BelongsToParentSelect::make('parent_id')
            ->label(__('inspirecms::inspirecms.parent_xxx', ['name' => strtolower(__('inspirecms::inspirecms.page'))]))
            ->validationAttribute(Str::lower(__('inspirecms::inspirecms.parent_xxx', ['name' => strtolower(__('inspirecms::inspirecms.page'))])))
            ->nestableParentRelationship(name: 'parent', titleAttribute: 'title', ignoreRecord: true)
            ->searchable(['title', 'slug'])
            ->preload()
            ->live()
            ->disabledOn('edit');
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Select
     */
    protected static function documentTypeSelectComponent()
    {
        $select = Forms\Components\Select::make('document_type_id')
            ->label(__('inspirecms::inspirecms.document_type'))
            ->validationAttribute(Str::lower(__('inspirecms::inspirecms.document_type')))
            ->searchable(['id'])
            ->preload()
            ->relationship(name: 'documentType', titleAttribute: 'title')
            ->required();

        // Load field group from document type
        $select
            ->live(debounce: 300)
            ->afterStateUpdated(fn ($component) => $component
                ->getContainer()                        // this field container
                ->getParentComponent()                  // tab
                ->getContainer()                        // tab's container
                ->getParentComponent()                  // tabs
                ->getContainer()                        // tabs's container
                ->getComponent('propertyData')          // find component by unique key in same level with section's container
                ->getChildComponentContainer()          // a container of "dynamicFieldGroups" fi-component
                ->fill())
            ->disabledOn('edit');

        return $select;
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getPropertyDataValueComponent()
    {
        return PropertyDataGroup::make()
            ->statePath('propertyData')
            ->columnSpanFull()
            ->dehydrated(false) // avoid fill into the model of content
            ->loadStateFromRelationshipsUsing(function (Model | CmsContent $record, $component) {
                $state = $record->getLatestPropertyData()?->property_value ?? [];
                $component->state($state);
            })
            ->saveRelationshipsUsing(function (Model | CmsContent $record, $state) {

                /** @var null|Model|CmsPropertyData */
                $latestPropertyData = $record->getLatestPropertyData();

                $latestPropertyDataIsDirty = true;

                // Check is dirty on "PropertyData" before save new version
                if ($latestPropertyData) {

                    $latestPropertyData->property_value = $state;

                    // Always "IsDirty", except checking specify attributes checking
                    if (! $latestPropertyData->isDirty(['property_value'])) {
                        $latestPropertyDataIsDirty = false;
                    }
                }

                if ($latestPropertyDataIsDirty) {
                    $record->createPropertyData([
                        'property_value' => $state,
                    ]);
                }
            });
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTimestampsGroupedFormComponent()
    {
        return Forms\Components\Section::make()
            ->schema([
                TimestampsGroup::make(),
            ])
            ->columns(['default' => 1]);
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getPublishDetailGroupedFormComponent()
    {
        return Forms\Components\Section::make()
            ->schema([
                static::getDisplayIsPublishedFormComponent(),
                static::getDisplayPublishedAtFormComponent(),
                static::getLatestPublishedAtFormComponent(),
            ])
            ->columns(['default' => 1]);
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getLatestPublishedAtFormComponent()
    {
        return Forms\Components\Placeholder::make('last_published_at')
            ->content(fn (Model | CmsContent | null $record) => $record?->getLatestPublishedPropertyData()?->published_at)
            ->label(__('inspirecms::inspirecms.last_published_at'))
            ->inlineLabel();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getDisplayPublishedAtFormComponent()
    {
        return Forms\Components\Placeholder::make('display_published_at')
            ->content(fn (Model | CmsContent | null $record) => $record?->published_at)
            ->label(__('inspirecms::inspirecms.publish_at'))
            ->inlineLabel();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getDisplayIsPublishedFormComponent()
    {
        return Forms\Components\Placeholder::make('display_is_published')
            ->label(__('inspirecms::inspirecms.is_published'))
            ->inlineLabel()
            ->extraAttributes(['class' => 'flex align-items-center h-full'])
            ->content(function (Model | CmsContent | null $record) {
                if (is_null($record)) {
                    return null;
                }

                return static::getBooleanIconPlaceholderComponentContent($record->isPublished(), trueIcon: 'heroicon-m-eye', falseIcon: 'heroicon-o-eye-slash');
            });
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getDisplayIsRootLevelFormComponent()
    {
        return Forms\Components\Placeholder::make('display_is_root')
            ->label(__('inspirecms::inspirecms.is_root_level'))
            ->content(function (Forms\Get $get) {
                $documentType = InspireCmsConfig::getDocumentTypeModelClass()::find($get('document_type_id'));
                if (is_null($documentType)) {
                    return null;
                }

                return static::getBooleanIconPlaceholderComponentContent($documentType->can_use_at_root);
            });
    }

    protected static function getBooleanIconPlaceholderComponentContent(bool $condition, string $trueIcon = 'heroicon-m-check-circle', string $falseIcon = 'heroicon-m-x-circle', string $trueColor = 'success', string $falseColor = 'danger'): HtmlString
    {
        return new HtmlString(Blade::render(<<<'blade'
            <x-filament::icon
                icon="{{$icon}}"
                class="h-5 w-5 text-custom-500 dark:text-custom-400"
                style="{{$iconStyle}}"
            >
            </x-filament::icon>
        blade, [
            'icon' => $condition ? $trueIcon : $falseIcon,
            'iconStyle' => \Filament\Support\get_color_css_variables(
                $condition ? $trueColor : $falseColor,
                shades: [400, 500],
                alias: 'infolists::components.icon-entry.item',
            ),
        ]));
    }

    //endregion Form field(s)/component(s)
}
