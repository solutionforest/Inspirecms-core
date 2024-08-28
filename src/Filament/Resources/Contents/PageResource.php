<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Enums\PageStatus;
use SolutionForest\InspireCms\Filament\Forms\Components\Actions\ResetAction;
use SolutionForest\InspireCms\Filament\Forms\Components\BelongsToParentSelect;
use SolutionForest\InspireCms\Filament\Forms\Components\PropertyDataGroup;
use SolutionForest\InspireCms\Filament\Forms\Components\RevertOrderGroup;
use SolutionForest\InspireCms\Filament\Forms\Components\TimestampsGroup;
use SolutionForest\InspireCms\Filament\Resources\Contents\PageResource\Contracts\HasPublishForm;
use SolutionForest\InspireCms\Filament\Resources\Contents\PageResource\Pages;
use SolutionForest\InspireCms\Models\CmsContent;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class PageResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

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
                        Forms\Components\Section::make()
                            ->columns(['default' => 1, 'lg' => 1, 'md' => 2])
                            ->schema([
                                static::getStatusFormComponent()
                                    // Always as "Draft" on `form`
                                    ->dehydrateStateUsing(fn () => PageStatus::Draft->value),
                                Forms\Components\Group::make()
                                    ->schema([

                                        TimestampsGroup::make(),
                                        
                                        Forms\Components\Placeholder::make('last_published_at')
                                            ->content(fn (Model | CmsContent $record) => $record->getLatestPublishedPropertyData()?->published_at?->toFormattedDateString())
                                            ->label(__('inspirecms::inspirecms.last_published_at')),
                                        // Forms\Components\Placeholder::make('display_status')
                                        //     ->content(fn (Model|CmsContent $record) => PageStatus::tryFrom($record->status)?->getLabel())
                                        //     ->label(__('inspirecms::inspirecms.status')),
                                        Forms\Components\Toggle::make('is_published')
                                            ->afterStateHydrated(function ($component, Model | CmsContent $record) {
                                                $component->state($record->isPublished());
                                            })
                                            ->dehydrated(false)
                                            ->disabled()
                                            ->inlineLabel()
                                            ->label(__('inspirecms::inspirecms.is_published')),
                                    ])
                                    ->visible(fn ($operation) => $operation == 'edit'),
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
                                    static::documentTypeSelectComponent(),
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
                    ->afterStateHydrated(fn (HasPublishForm $livewire, $component) => $component->state($livewire->getPublishableFormDataBeforePublish())),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with([
                'propertyDatas',  // To get latest version
            ]))
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id'))
                    ->width('1%')->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::inspirecms.title'))
                    ->sortable(),
                Tables\Columns\ColumnGroup::make(__('inspirecms::inspirecms.visibility'))
                    ->columns([

                        Tables\Columns\TextColumn::make('status')
                            ->label(__('inspirecms::inspirecms.status'))
                            ->formatStateUsing(fn ($state) => PageStatus::tryFrom($state)?->getLabel() ?? '')
                            ->color(fn ($state) => PageStatus::tryFrom($state)?->getColor() ?? 'gray')
                            ->badge()
                            ->icon(fn (Model | CmsContent $record) => $record->isPublished() ? 'heroicon-m-eye' : null)
                            ->iconPosition(IconPosition::After),

                        Tables\Columns\TextColumn::make('published_at')
                            ->label(__('inspirecms::inspirecms.publish_at')),
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }

    //region Form field(s)/component(s)
    protected static function getTitleFormComponent(): Forms\Components\Component
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

    protected static function getSlugFormComponent(): Forms\Components\Component
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

    protected static function getPublishedAtComponent(): Forms\Components\Component
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

    protected static function getStatusFormComponent(): Forms\Components\Component
    {
        return Forms\Components\Hidden::make('status')
            ->default(PageStatus::Draft->value)
            ->dehydratedWhenHidden(true);
    }

    protected static function getParentPageFormComponent(): Forms\Components\Component
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

    protected static function getPropertyDataValueComponent(): Forms\Components\Component
    {
        return PropertyDataGroup::make()
            ->statePath('propertyData')
            ->columnSpanFull()
            ->loadStateFromRelationshipsUsing(function (Model | CmsContent $record, $component) {
                $state = $record->getLatestPropertyData()?->property_value ?? [];
                $component->state($state);
            })
            ->saveRelationshipsUsing(function (Model | CmsContent $record, $state) {
                $record->createPropertyData([
                    'property_value' => $state,
                ]);
            });
    }

    //endregion Form field(s)/component(s)

    public static function getModel(): string
    {
        return InspireCmsConfig::getContentModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.page');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('inspirecms::inspirecms.content');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['propertyDatas']);
    }
}
