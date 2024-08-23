<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Enums\PageStatus;
use SolutionForest\InspireCms\Filament\Forms\Components\Actions\ResetAction;
use SolutionForest\InspireCms\Filament\Forms\Components\BelongsToParentSelect;
use SolutionForest\InspireCms\Filament\Forms\Components\PropertyDataGroup;
use SolutionForest\InspireCms\Filament\Resources\Contents\PageResource\Pages;
use SolutionForest\InspireCms\Models\CmsContent;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class PageResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([

                Forms\Components\Tabs::make()
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('inspirecms::inspirecms.general'))
                            ->schema([
                                static::getTitleFormComponent(),
                                static::getParentPageFormComponent(),
                                static::documentTypeSelectComponent(),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('inspirecms::inspirecms.setting'))
                            ->schema([
                                static::getSlugFormComponent(),
                            ]),
                    ]),

                // Field group grouped component
                static::getPropertyDataValueComponent(),
            ]);
    }

    public static function detailInfoForm(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Section::make()
                    ->columns(['default' => 1, 'lg' => 1, 'md' => 2])
                    ->schema([
                        static::getStatusFormComponent(),
                        static::getPublishedAtComponent(),
                        Forms\Components\Placeholder::make('last_updated_at')
                            ->content(fn ($record) => $record->updated_at?->shortRelativeToNowDiffForHumans())
                            ->visible(fn ($operation) => $operation == 'edit')
                            ->label(__('inspirecms::inspirecms.last_updated_at')),
                        Forms\Components\Placeholder::make('to_do:have_publisjed_version?'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with([
                'latestPropertyDatas',  // To get latest version
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

                        Tables\Columns\TextColumn::make('current_version')
                            ->label(__('inspirecms::inspirecms.current_version'))
                            ->getStateUsing(fn (Model | CmsContent $record) => $record->getVersioningStatus()?->getLabel() ?? __('inspirecms::inspirecms.n/a'))
                            ->color(fn (Model | CmsContent $record) => $record->getVersioningStatus()?->getColor() ?? 'gray')
                            ->badge(),

                        Tables\Columns\TextColumn::make('published_at')
                            ->label(__('inspirecms::inspirecms.publish_at')),
                    ]),
                Tables\Columns\TextColumn::make('created_at')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->sortable(),
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
        return Forms\Components\Grid::make(2)
            ->columnSpanFull()
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label(__('inspirecms::inspirecms.title'))
                    ->validationAttribute(Str::lower(__('inspirecms::inspirecms.title')))
                    ->live(debounce: 300)->afterStateUpdated(function ($state, $get, $set, $operation) {
                        // Fill slug if empty / operation is create
                        if ($operation === 'create' || empty($get('slug'))) {
                            $set('slug', Str::slug($state));
                        }
                    })
                    ->required(),
            ]);
    }

    protected static function getSlugFormComponent(): Forms\Components\Component
    {
        return Forms\Components\TextInput::make('slug')
            ->label(__('inspirecms::inspirecms.slug'))
            ->live(debounce: 300)->afterStateUpdated(fn ($component, $state) => $component->state(Str::slug($state)))
            ->unique(column: 'slug', ignoreRecord: true, modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule, callable $get) {
                $parentId = $get('parent_id');
                if ($parentId) {
                    return $rule->where('parent_id', $parentId);
                } else {
                    return $rule->whereNull('parent_id');
                }
            })
            ->required();
    }

    protected static function getPublishedAtComponent(): Forms\Components\Component
    {
        return Forms\Components\DateTimePicker::make('published_at')
            ->label(__('inspirecms::inspirecms.publish_at'))
            ->native(false)
            ->disabled(function ($get) {
                if (
                    // force as current time and cannot change if 'Publish'
                    $get('status') === PageStatus::Publish->value
                ) {
                    return true;
                }

                return false;
            })
            // save data the field is disabled
            ->dehydrated(true)->dehydratedWhenHidden(true)
            // // guard before save
            // ->dehydrateStateUsing(function ($state, $get) {
            //     if ($get('status') === PageStatus::Pending->value) {
            //         $state = null;
            //     }
            //     return $state;
            // })
            ->suffixAction(ResetAction::make())
            ->hintIcon(
                'heroicon-o-information-circle',
                __('inspirecms::inspirecms.hints.future_publish')
            )
            // Required for Publish/SchedulePublish
            ->required(fn ($get) => in_array($get('status'), [
                PageStatus::Publish->value,
                PageStatus::SchedulePublish->value,
            ]));
    }

    protected static function getStatusFormComponent(): Forms\Components\Component
    {
        return Forms\Components\Select::make('status')
            ->label(__('inspirecms::inspirecms.status'))
            ->options(PageStatus::class)
            ->default(PageStatus::Pending->value)
            ->live()->afterStateUpdated(function ($state, Forms\Set $set, $operation) {
                // fill publish time as now is the status change to "Publish"
                if ($state == PageStatus::Publish->value) {
                    $set('published_at', now());
                } elseif ($operation === 'create' && $state == PageStatus::Pending->value) {
                    $set('published_at', null);
                }
            })
            ->native(false)
            ->required();
    }

    protected static function getParentPageFormComponent(): Forms\Components\Component
    {
        return BelongsToParentSelect::make('parent_id')
            ->label(__('inspirecms::inspirecms.parent_xxx', ['name' => strtolower(__('inspirecms::inspirecms.page'))]))
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
            ->loadStateFromRelationshipsUsing(function ($record, $component) {
                $state = $record->latestPropertyData?->property_value ?? [];
                $component->state($state);
            })
            ->saveRelationshipsUsing(function ($record, $state, $get) {
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
}
