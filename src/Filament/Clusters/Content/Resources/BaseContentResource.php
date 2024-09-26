<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\ContentForm;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\HasPublishForm;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Forms\Components\Actions\ResetAction;
use SolutionForest\InspireCms\Filament\Forms\Components\BelongsToParentSelect;
use SolutionForest\InspireCms\Filament\Forms\Components\RevertOrderGroup;
use SolutionForest\InspireCms\Filament\Forms\Components\TimestampsGroup;
use SolutionForest\InspireCms\Helpers\KeyHelper;
use SolutionForest\InspireCms\Helpers\UIHelper;
use SolutionForest\InspireCms\Models\Contracts\Content as ModelsContent;
use SolutionForest\InspireCms\Models\Contracts\PropertyData;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

abstract class BaseContentResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    public static function getBasePermissionPrefixes(): array
    {
        return [
            'view_any',
            'view',
            'create',
            'update',
            'delete',
            'delete_any',
            'restore',
            'restore_any',
            'force_delete',
            'force_delete_any',
            'publish',
            'unpublish',
            'set_private',
        ];
    }

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
                                static::getTemplateFormComponent(),
                                static::getParentPageFormComponent(),
                                static::getDocumentTypeFormComponent(),
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
                                ->columns(1)
                                ->schema([
                                    static::getTitleFormComponent(),
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
            ->modifyQueryUsing(fn ($query) => $query->with('parent'))
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id'))
                    ->width('1%')->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::inspirecms.title'))
                    ->sortable()
                    ->grow(),
                Tables\Columns\TextColumn::make('parent.title')
                    ->label(__('inspirecms::inspirecms.parent'))
                    ->grow(),

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
                            ->getStateUsing(fn (Model | ModelsContent $record) => $record->isPublished())  // Already include private
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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ])->iconButton(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
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
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // BaseContentChildrenRelationManager::class,
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getContentModelClass();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'propertyDatas', // To get latest version
            'documentType', // For template use
            'parent', // To get parent title
        ])
        ->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);;
    }

    //region Form field(s)/component(s)
    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTitleFormComponent()
    {
        return Forms\Components\TextInput::make('title')
            ->label(__('inspirecms::inspirecms.title'))
            ->live()->afterStateUpdated(function ($state, $get, $set, $operation) {
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
            ->live()->afterStateUpdated(fn ($component, $state) => $component->state(Str::slug($state)))
            ->unique(table: static::getModel(), column: 'slug', ignoreRecord: true, modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule, callable $get) {
                return $rule->where('parent_id', $get('parent_id') ?? 0);
            })
            ->required();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    public static function getPublishedAtComponent()
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
        $fallbackParentId = KeyHelper::generateMinUuid();

        return BelongsToParentSelect::make('parent_id')
            ->label(__('inspirecms::inspirecms.parent_xxx', ['name' => strtolower(__('inspirecms::inspirecms.page'))]))
            ->nestableParentRelationship(name: 'parent', titleAttribute: 'title', ignoreRecord: true)
            ->searchable(['title', 'slug'])
            ->preload()
            ->live()
            ->disabled()
            ->dehydrated(true)
            ->hidden(function ($operation) {
                return $operation === 'create';
            })
            ->dehydratedWhenHidden(true)
            ->rootParentId($fallbackParentId)
            ->dehydrateStateUsing(function (ContentForm $livewire, $operation, $record) use ($fallbackParentId) {
                if ($operation === 'create') {
                    return $livewire->getParentKey() ?? $fallbackParentId;
                }

                return $record?->parent_id ?? $fallbackParentId;
            });
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTemplateFormComponent()
    {
        return Forms\Components\Select::make('template_id')
            ->label(__('inspirecms::inspirecms.template'))
            ->options(function (ContentForm $livewire) {
                $documentType = $livewire->getDocumentType();
                if (! $documentType instanceof Model) {
                    $documentType = InspireCmsConfig::getDocumentTypeModelClass()::query()
                        ->with(['templates'])
                        ->find($documentType);
                } else {
                    $documentType->loadMissing('templates');
                }
                if (! $documentType) {
                    return [];
                }

                return collect($documentType->templates)
                    ->mapWithKeys(function ($template) {
                        return [$template->getKey() => $template->slug];
                    })
                    ->all();
            })
            ->searchable()
            ->dehydrated(false)
            ->saveRelationshipsUsing(function (ModelsContent $record, $state) {
                if ($state) {
                    $record->templates()->sync($state);
                    $record->setAsDefaultTemplate($state);
                } else {
                    $record->templates()->sync([]);
                }
            });
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Select
     */
    protected static function getDocumentTypeFormComponent()
    {
        return Forms\Components\Hidden::make('document_type_id')
            ->dehydratedWhenHidden()
            ->dehydrateStateUsing(function (ContentForm $livewire, $record) {
                $documentTypeId = $record?->document_type_id ?? null;
                if (! $documentTypeId) {
                    $documentType = $livewire->getDocumentType();
                    $documentTypeId = $documentType instanceof Model ? $documentType->getKey() : $documentType;
                }

                return $documentTypeId;
            });
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Select
     */
    protected static function documentTypeSelectComponent()
    {
        return Forms\Components\Hidden::make('document_type_id')
            ->dehydratedWhenHidden();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getPropertyDataValueComponent()
    {
        $getFieldGroupsFromDocumentType = function (int | string | Model | null $documentType) {

            if ($documentType instanceof Model) {

            } elseif (is_null($documentType)) {
                return collect();
            } else {

                $documentType = InspireCmsConfig::getDocumentTypeModelClass()::query()
                    ->with(['fieldGroups'])
                    ->whereHas('fieldGroups')
                    ->find($documentType);

                if (! $documentType) {
                    return collect();
                }
            }

            return $documentType->fieldGroups ?? collect();
        };

        return Forms\Components\Group::make()
            ->key('propertyData')
            ->statePath('propertyData')
            ->columnSpanFull()
            ->dehydrated(false)
            ->schema(function (ContentForm $livewire, $record, $operation) use ($getFieldGroupsFromDocumentType) {
                $fieldGroups = $record
                    ? $record->documentType->fieldGroups
                    : $getFieldGroupsFromDocumentType($livewire->getDocumentType() ?? null);
                $groupComponents = [];

                foreach ($fieldGroups as $fieldGroupModel) {

                    $groupComponents[] = $fieldGroupModel->toFilamentComponent();
                }

                return $groupComponents;
            })
            ->dehydrateStateUsing(fn ($component) => $component->getState())
            ->loadStateFromRelationshipsUsing(function (Model | ModelsContent $record, $component) {
                $state = $record->getLatestPropertyData()?->property_value ?? [];
                $component->state($state);
            })
            ->saveRelationshipsUsing(function (Model | ModelsContent $record, Forms\Components\Group $component) {

                $state = $component->getState();

                /** @var null|Model|PropertyData */
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
                Forms\Components\Placeholder::make('path')
                    ->content(fn (ModelsContent $record) => $record->generateFullSlug())
                    ->extraAttributes(['class' => 'text-sm font-mono'])
                    ->inlineLabel(),
            ])
            ->columns(['default' => 1]);
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getLatestPublishedAtFormComponent()
    {
        return Forms\Components\Placeholder::make('last_published_at')
            ->content(fn (Model | ModelsContent | null $record) => $record?->getLatestPublishedPropertyData()?->published_at)
            ->label(__('inspirecms::inspirecms.last_published_at'))
            ->inlineLabel();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getDisplayPublishedAtFormComponent()
    {
        return Forms\Components\Placeholder::make('display_published_at')
            ->content(fn (Model | ModelsContent | null $record) => $record?->published_at)
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
            ->content(function (Model | ModelsContent | null $record) {
                if (is_null($record)) {
                    return null;
                }

                return UIHelper::getBooleanIconPlaceholder($record->isPublished(), trueIcon: 'heroicon-m-eye', falseIcon: 'heroicon-o-eye-slash', falseColor: 'gray');
            });
    }

    //endregion Form field(s)/component(s)
}
