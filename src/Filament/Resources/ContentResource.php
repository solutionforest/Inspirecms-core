<?php

namespace SolutionForest\InspireCms\Filament\Resources;

use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Pboivin\FilamentPeek\Livewire\BuilderEditor;
use SolutionForest\InspireCms\Base\Enums\SitemapChangeFrequency;
use SolutionForest\InspireCms\Base\Filament\Contracts\ContentForm;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseContentListTrashPage;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseContentViewPage;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Dtos\LanguageDto;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Factories\ContentSlugFactory;
use SolutionForest\InspireCms\Filament\Clusters\Content;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Forms\Components\Actions\ResetAction;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentPicker;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentTree;
use SolutionForest\InspireCms\Filament\Forms\Components\TimestampsGroup;
use SolutionForest\InspireCms\Filament\Resources\ContentResource\Pages;
use SolutionForest\InspireCms\Filament\Resources\ContentResource\RelationManagers\ChildrenRelationManager;
use SolutionForest\InspireCms\Filament\Resources\ContentResource\Widgets;
use SolutionForest\InspireCms\Filament\Resources\Helpers\ContentResourceHelper;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Helpers\SearchHelper;
use SolutionForest\InspireCms\Helpers\SeoHelper;
use SolutionForest\InspireCms\Helpers\UIHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content as ModelsContent;
use SolutionForest\InspireCms\Support\Helpers\KeyHelper;
use SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components\MediaPicker;
use SolutionForest\InspireCms\Support\Models\Scopes\NestableTreeDetailScope;

class ContentResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;
    use Translatable;

    protected static ?int $navigationSort = -9;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $cluster = Content::class;

    protected static ?string $slug = 'pages';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
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
            'reorder_children',
            'view_history',
            'set_as_default',
            'lock',
            'rollback_version',
        ];
    }

    public static function getTranslatableLocales(): array
    {
        return array_keys(InspireCms::getAllAvailableLanguages());
    }

    public static function canAccess(): bool
    {
        $cluster = static::getClusterSection();
        $permissionName = ! blank($cluster) ? $cluster::getAccessRightPermissionName() : null;
        if (! blank($permissionName)) {
            return filament()->auth()->user()?->can($permissionName) ?? false;
        }

        return false;
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->disabled(function (null | Model | ModelsContent $record, $livewire) {
                if ($record?->isLocked()) {
                    return true;
                }
                if ($livewire instanceof ViewRecord) {
                    return true;
                }

                return false;
            })
            ->schema([
                Forms\Components\Actions::make([
                    \Pboivin\FilamentPeek\Forms\Actions\InlinePreviewAction::make()
                        ->label(__('inspirecms::buttons.preview.label'))
                        ->builderName('propertyData'),
                ])
                    ->alignEnd()
                    ->hidden(function (null | Model | ModelsContent $record, $livewire) {
                        if ($record?->isLocked()) {
                            return true;
                        }

                        return $livewire instanceof BaseContentViewPage;
                    }),
                Forms\Components\Tabs::make()
                    ->persistTabInQueryString()
                    ->contained(false)
                    ->tabs(function (ContentForm $livewire) {

                        $documentType = $livewire->getDocumentType();

                        $tabs[] = static::getPropertyDataValueComponent(isTab: true);

                        if ($documentType->display_category != \SolutionForest\InspireCms\Base\Enums\DocumentTypeCategory::Data) {
                            $tabs[] = Forms\Components\Tabs\Tab::make('seo')
                                ->label(__('inspirecms::resources/content.tabs.seo'))
                                ->schema([
                                    Forms\Components\Section::make()
                                        ->columns(1)
                                        ->heading(__('inspirecms::resources/content.sections.general.heading'))
                                        ->aside()
                                        ->schema([
                                            static::getTitleFormComponent(),
                                            static::getSlugFormComponent(),
                                        ]),
                                    static::getSeoFormComponent(),
                                ]);
                            $tabs[] = Forms\Components\Tabs\Tab::make('sitemap')
                                ->label(__('inspirecms::resources/content.tabs.sitemap'))
                                ->schema([
                                    static::getSitemapFormComponent(),
                                ]);
                        }
                        $tabs[] = Forms\Components\Tabs\Tab::make('details')
                            ->label(__('inspirecms::resources/content.tabs.details'))
                            ->columns(3)
                            ->schema([
                                Forms\Components\Section::make()
                                    ->columns(1)
                                    ->columnSpan(1)
                                    ->schema([
                                        static::getTemplateFormComponent(),
                                        static::getParentPageFormComponent(),
                                        static::getDocumentTypeFormComponent(),
                                    ]),
                                Forms\Components\Group::make()
                                    ->columns(1)
                                    ->columnSpan(2)
                                    ->schema([
                                        ...(
                                            $documentType->display_category != \SolutionForest\InspireCms\Base\Enums\DocumentTypeCategory::Data ?
                                            [] :
                                            [
                                                Forms\Components\Section::make([
                                                    static::getTitleFormComponent(),
                                                    static::getSlugFormComponent(),
                                                ]),
                                            ]
                                        ),
                                        Forms\Components\Section::make([
                                            static::getDisplayDocumentTypeComponent(),
                                            static::getDisplayParentFormComponent(),
                                            static::getDisplayKeyFormComponent(),
                                            static::getDisplayUrlFormComponent()->visible($documentType->display_category != \SolutionForest\InspireCms\Base\Enums\DocumentTypeCategory::Data),
                                        ]),
                                        Forms\Components\Group::make()
                                            ->visible(fn ($record) => $record != null)
                                            ->schema([
                                                static::getTimestampsGroupedFormComponent()->columnSpan(1),
                                                static::getPublishDetailGroupedFormComponent()->columnSpan(1),
                                                static::getLockDetailGroupedFormComponent()->columnSpan(1),
                                            ]),
                                    ]),
                            ]);

                        return $tabs;
                    }),
            ]);
    }

    public static function publishForm(Form $form): Form
    {
        return $form
            ->schema([
                static::getPublishedAtFormComponent(),
                Forms\Components\Group::make()
                    ->statePath('formData')
                    // Here can validate form data
                    ->afterStateHydrated(fn (ContentForm $livewire, $component) => $component->state($livewire->getPublishableFormDataBeforePublish([]))),
            ]);
    }

    public static function getPreviewBuilderEditorSchema(string $builderName): Forms\Components\Component | array
    {
        $langs = collect(InspireCms::getAllAvailableLanguages())
            ->mapWithKeys(fn (LanguageDto $lang) => [$lang->code => $lang->getLabel()])
            ->all();

        return [
            Forms\Components\Select::make('activeLocale')
                ->options($langs)
                ->afterStateHydrated(fn ($component) => $component->state(array_key_first($langs)))
                ->selectablePlaceholder(false)
                ->prefixIcon(FilamentIcon::resolve('inspirecms::language'))
                ->hiddenLabel()
                ->suffix(function ($state) {
                    if (! $state) {
                        return null;
                    }
                    $lang = collect(InspireCms::getAllAvailableLanguages())->get($state);
                    if (! $lang || ! $lang?->isDefault) {
                        return null;
                    }

                    return __('inspirecms::inspirecms.default');
                })
                ->live(),
            static::getPropertyDataValueComponent(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('nestable_tree_order', 'asc')
            ->modifyQueryUsing(function ($query, $livewire) {
                $query
                    ->with('publishedVersions')
                    ->withGlobalScope(NestableTreeDetailScope::class, new NestableTreeDetailScope);
                if ($livewire instanceof ChildrenRelationManager) {
                    $query->with('parent');
                }

                return $query;
            })
            ->columns([

                Tables\Columns\ViewColumn::make('documentType.icon')
                    ->label('')
                    ->view('inspirecms::filament.tables.columns.guava-icon', ['height' => 5])
                    ->extraAttributes(['class' => 'text-gray-500 dark:text-gray-400'])
                    ->alignCenter()->verticallyAlignCenter()
                    ->tooltip(fn (Model | ModelsContent $record) => $record->documentType?->title)
                    ->width('1%'),

                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id'))
                    ->width('1%')->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label(__('inspirecms::resources/content.deleted_at.label'))
                    ->sortable()
                    ->formatStateUsing(fn (?\Carbon\Carbon $state) => $state?->diffForHumans())
                    ->visibleOn([BaseContentListTrashPage::class])
                    ->width('5%'),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::resources/content.title.label'))
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->limit(20)->tooltip(fn ($state) => $state),

                Tables\Columns\TextColumn::make('slug')
                    ->label(__('inspirecms::resources/content.slug.label'))
                    ->searchable(isIndividual: true)
                    ->fontFamily('mono')
                    ->limit(20)->tooltip(fn ($state) => $state),

                Tables\Columns\TextColumn::make('nestable_tree_order')
                    ->label(__('inspirecms::inspirecms.order'))
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('parent')
                    ->label(__('inspirecms::resources/content.parent.label'))
                    ->getStateUsing(function (Model | ModelsContent $record) {
                        if ($record->isRootLevel()) {
                            return null;
                        }

                        return $record->parent?->title ?? $record->parent_id;
                    })
                    ->grow()
                    ->toggleable(),

                Tables\Columns\ColumnGroup::make(__('inspirecms::resources/content.visibility.label'))
                    ->columns([

                        Tables\Columns\TextColumn::make('display_status')
                            ->label(__('inspirecms::resources/content.status.label'))
                            ->formatStateUsing(fn (?ContentStatusOption $state) => $state->getLabel())
                            ->color(fn (?ContentStatusOption $state) => $state->getColor())
                            ->icon(fn (?ContentStatusOption $state) => $state->getIcon())
                            ->badge()
                            ->iconPosition(IconPosition::Before)
                            ->width('2%'),

                        Tables\Columns\IconColumn::make('is_published')
                            ->label(__('inspirecms::resources/content.is_published.label'))
                            ->getStateUsing(fn (Model | ModelsContent $record) => $record->isPublished())
                            ->boolean()
                            ->width('2%')
                            ->trueIcon(FilamentIcon::resolve('inspirecms::visible'))
                            ->falseIcon(FilamentIcon::resolve('inspirecms::invisiable'))
                            ->falseColor('gray')
                            ->alignCenter()->verticallyAlignCenter()
                            ->hiddenOn([BaseContentListTrashPage::class]),

                        Tables\Columns\TextColumn::make('published_at')
                            ->label(__('inspirecms::resources/content.published_at.label'))
                            ->getStateUsing(fn (Model | ModelsContent $record) => ContentResourceHelper::getLatestPublishTime($record)?->diffForHumans())
                            ->width('5%')
                            ->hiddenOn([BaseContentListTrashPage::class]),
                    ]),

                // timestamps
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('inspirecms::resources/content.created_at.label'))
                    ->sortable()
                    ->formatStateUsing(fn (?\Carbon\Carbon $state) => $state?->diffForHumans())
                    ->width('5%'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('inspirecms::resources/content.updated_at.label'))
                    ->sortable()
                    ->formatStateUsing(fn (?\Carbon\Carbon $state) => $state?->diffForHumans())
                    ->width('5%'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton()->visible(fn (Model | ModelsContent $record) => ! $record->trashed()),
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn ($livewire) => ! $livewire instanceof BaseContentListTrashPage),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ])->iconButton(),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (Model | ModelsContent $record): bool => static::canDelete($record),
            )
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label(__('inspirecms::resources/content.is_published.label'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereIsPublished(condition: true),
                        false: fn (Builder $query) => $query->whereIsPublished(condition: false),
                        blank: fn (Builder $query) => $query,
                    ),
                Tables\Filters\TernaryFilter::make('is_root_level')
                    ->label(__('inspirecms::resources/content.is_root_level.label'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereIsRoot(condition: true),
                        false: fn (Builder $query) => $query->whereIsRoot(condition: false),
                        blank: fn (Builder $query) => $query,
                    )
                    ->hiddenOn([ChildrenRelationManager::class]),
                Tables\Filters\QueryBuilder::make()
                    ->constraints([
                        Tables\Filters\QueryBuilder\Constraints\TextConstraint::make('documentType')
                            ->label(__('inspirecms::inspirecms.document_type.singular'))
                            ->relationship(name: 'documentType', titleAttribute: 'title'),
                    ]),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContentCollapsible);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContentRecords::route('/'),
            'create' => Pages\CreateContentRecord::route('/create'),
            'edit' => Pages\EditContentRecord::route('/{record}/edit'),
            'view' => Pages\ViewContentRecord::route('/{record}/view'),
            'trash' => Pages\ListTrashedContentRecords::route('/trashes'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            ChildrenRelationManager::make(),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\ContentPageOverview::class,
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
            'publishedVersions', // To get published version, and determine is published
            'documentType.templates', // For template use
            'parent', // To get parent title
            'webSetting',
            'sitemap',
        ])
            ->whereHas('documentType', fn ($query) => $query->whereCanBeContent());
    }

    public static function resolveRecordRouteBinding(int | string $key): ?Model
    {
        return app(static::getModel())
            ->resolveRouteBindingQuery(
                static::getEloquentQuery()
                    ->with([
                        'documentType',
                    ])
                    ->withoutGlobalScopes([
                        SoftDeletingScope::class,
                    ]),
                $key,
                static::getRecordRouteKeyName()
            )
            ->first();
    }

    public static function getRecordRouteKeyName(): ?string
    {
        return app(static::getModel())->getQualifiedKeyName();
    }

    // region Global search
    public static function getGloballySearchableAttributes(): array
    {
        /**
         * @var Model
         */
        $model = app(static::getModel());

        return [$model->qualifyColumn('id'), 'slug'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return UIHelper::generateTextWithBadge(
            text: static::getRecordTitle($record),
            badgeText: $record->slug,
            attributes: [
                'text' => ['class' => 'flex-1 font-semibold'],
                'badge' => ['class' => 'font-mono'],
            ]
        );
    }

    /**
     * @param  array<string>  $searchAttributes
     */
    protected static function applyGlobalSearchAttributeConstraint(Builder $query, string $search, array $searchAttributes, bool &$isFirst): Builder
    {
        return SearchHelper::globalSearchWithRelation(
            query: $query,
            search: $search,
            searchAttributes: $searchAttributes,
            isForcedCaseInsensitive: static::isGlobalSearchForcedCaseInsensitive(),
            isFirst: $isFirst
        );
    }
    // endregion Global search

    // region Form field(s)/component(s)
    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTitleFormComponent()
    {
        return Forms\Components\TextInput::make('title')
            ->label(__('inspirecms::resources/content.title.label'))
            ->validationAttribute(__('inspirecms::resources/content.title.validation_attribute'))
            ->placeholder(__('inspirecms::resources/content.title.placeholder'))
            ->helperText(__('inspirecms::resources/content.title.instructions'))
            ->live(true, 5000)->afterStateUpdated(function ($state, $get, $set, $operation, ContentForm $livewire) {
                // Fill slug if empty / operation is create
                if ($operation === 'create' || empty($get('slug'))) {
                    $set('slug', ContentSlugFactory::create()->generate($state));
                }
                $locale = $livewire->getActiveActionsLocale();
                $set("webSetting.seo.meta_title.{$locale}", $state);
            })
            ->autofocus()
            ->required()
            ->limitLengthWithHint(60)
            ->translatable();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getSlugFormComponent()
    {
        return Forms\Components\TextInput::make('slug')
            ->label(__('inspirecms::resources/content.slug.label'))
            ->validationAttribute(__('inspirecms::resources/content.slug.validation_attribute'))
            ->placeholder(__('inspirecms::resources/content.slug.placeholder'))
            ->helperText(__('inspirecms::resources/content.slug.instructions'))
            ->live(true, 5000)
            ->afterStateUpdated(function ($component, $state) {
                return $component->state(ContentSlugFactory::create()->generate($state));
            })
            ->unique(table: static::getModel(), column: 'slug', ignoreRecord: true, modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule, callable $get, ContentForm $livewire, string $operation) {
                $model = new (static::getModel());

                $parentId = $get('parent_id') ?? null;

                if ($operation === 'create') {
                    $parentId = $livewire->getParentKey() ?? $parentId;
                }

                if (! filled($parentId)) {
                    $parentId = $model->getRootLevelParentId();
                }

                return $rule
                    ->where('parent_id', $parentId);
            })
            ->required();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getParentPageFormComponent()
    {
        $fallbackParentId = KeyHelper::generateMinUuid();

        return Forms\Components\Hidden::make('parent_id')
            ->dehydratedWhenHidden()
            ->dehydrateStateUsing(function (ContentForm $livewire, $operation, null | Model | ModelsContent $record) use ($fallbackParentId) {
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
            ->label(__('inspirecms::resources/content.template.label'))
            ->validationAttribute(__('inspirecms::resources/content.template.validation_attribute'))
            ->helperText(__('inspirecms::resources/content.template.instructions'))
            ->options(function (ContentForm $livewire) {
                $documentType = $livewire->getDocumentType()?->loadMissing('templates');
                if (! $documentType) {
                    return [];
                }

                return collect($documentType->templates)
                    ->mapWithKeys(function ($template) use ($documentType) {

                        $label = $template->slug;

                        if ($template->getKey() === $documentType->getDefaultTemplate()?->getKey()) {
                            $label .= ' [' . __('inspirecms::inspirecms.default') . ']';
                        }

                        return [
                            $template->getKey() => $label,
                        ];
                    })
                    ->all();
            })
            ->dehydrated(false)
            ->saveRelationshipsUsing(function (Model | ModelsContent $record, $state) {
                if ($state) {
                    $record->templates()->sync($state);
                    $record->setAsDefaultTemplate($state);
                } else {
                    $record->templates()->sync([]);
                }
            })
            ->loadStateFromRelationshipsUsing(function (Model | ModelsContent $record, $component) {
                if ($template = $record?->getDefaultTemplate()) {
                    $component->state($template->getKey());
                }
            });
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Select
     */
    protected static function getDocumentTypeFormComponent()
    {
        return Forms\Components\Hidden::make('document_type_id')
            ->validationAttribute(__('inspirecms::resources/content.document_type.validation_attribute'))
            ->dehydratedWhenHidden()
            ->dehydrateStateUsing(function (ContentForm $livewire, null | Model | ModelsContent $record) {
                return $record?->document_type_id ?? $livewire->getDocumentType()?->getKey();
            });
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Select
     */
    protected static function getDisplayDocumentTypeComponent()
    {
        return Forms\Components\Placeholder::make('display_document_type')
            ->label(__('inspirecms::resources/content.document_type.label'))
            ->inlineLabel()
            ->content(function (Model | ModelsContent | null $record, ContentForm $livewire) {
                if ($record) {
                    $documentType = $record->documentType;
                    $text = $documentType->title;
                } else {
                    $documentType = $livewire->getDocumentType();
                    $text = $documentType?->title;
                }

                if (! filled($text)) {
                    $text = __('inspirecms::inspirecms.n/a');
                }
                $url = $documentType ? FilamentResourceHelper::attemptToGetUrl(
                    resource: InspireCmsConfig::getFilamentResource('document_type', DocumentTypeResource::class),
                    pages: ['edit', 'view'],
                    parameters: ['record' => $documentType],
                    autorizeAction: true
                ) : null;
                if (! $url) {
                    return $text;
                }

                return UIHelper::generateTextWithIconButton(
                    text: $text,
                    icon: FilamentIcon::resolve('inspirecms::goto'),
                    color: 'gray',
                    size: 'sm',
                    url: $url,
                    linkTarget: '_blank',
                );
            });
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getPropertyDataValueComponent(bool $isTab = false)
    {
        $getFieldGroupsFromDocumentType = function (int | string | Model | null $documentType) {

            if ($documentType instanceof Model) {

                $documentType->loadMissing('fieldGroups.fields');

            } elseif (! is_null($documentType)) {

                $documentType = InspireCmsConfig::getDocumentTypeModelClass()::query()
                    ->with(['fieldGroups.fields']) // build filament fields
                    ->whereHas('fieldGroups')
                    ->find($documentType);
            }

            if (! $documentType) {
                return collect();
            }

            return collect($documentType->fieldGroups)->sortBy('pivot.order')->values();
        };

        $getFieldGroupsFromLivewireOrRecord = function (ContentForm | BuilderEditor $livewire, null | Model | ModelsContent $record) use ($getFieldGroupsFromDocumentType) {
            if ($record) { // edit/view page
                $record->documentType?->loadMissing('fieldGroups.fields');
                $fieldGroups = collect($record->documentType->fieldGroups)->sortBy('pivot.order')->values();
            } elseif ($livewire instanceof ContentForm) { // create
                $fieldGroups = $getFieldGroupsFromDocumentType($livewire->getDocumentType() ?? null);
            } elseif ($livewire instanceof BuilderEditor) { // preview builder
                $fieldGroups = $getFieldGroupsFromDocumentType($livewire->editorData['documentType'] ?? null);
            } else {
                $fieldGroups = collect();
            }

            return $fieldGroups;
        };

        $schema = function (ContentForm | BuilderEditor $livewire, null | Model | ModelsContent $record) use ($getFieldGroupsFromLivewireOrRecord) {
            $fieldGroups = $getFieldGroupsFromLivewireOrRecord($livewire, $record);

            $groupComponents = [];

            foreach ($fieldGroups as $fieldGroupModel) {

                $groupComponents[] = $fieldGroupModel->toFilamentComponent();

            }

            return $groupComponents;
        };

        if ($isTab) {

            return Forms\Components\Tabs\Tab::make('content')
                ->label(__('inspirecms::resources/content.tabs.content'))
                ->visible(fn ($livewire, $record) => count($getFieldGroupsFromLivewireOrRecord($livewire, $record)) > 0)
                ->key('propertyData')
                ->statePath('propertyData')
                ->dehydratedWhenHidden()
                ->dehydrateStateUsing(fn ($component) => $component->getState())
                ->schema($schema);
        }

        return Forms\Components\Group::make()
            ->key('propertyData')
            ->statePath('propertyData')
            ->columnSpanFull()
            ->schema($schema)
            ->dehydrateStateUsing(fn ($component) => $component->getState());
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
                Forms\Components\Placeholder::make('display_is_published')
                    ->label(__('inspirecms::resources/content.is_published.label'))
                    ->inlineLabel()
                    ->extraAttributes(['class' => 'flex align-items-center h-full'])
                    ->content(function (Model | ModelsContent | null $record) {
                        if (is_null($record)) {
                            return null;
                        }

                        return UIHelper::generateBooleanIcon($record->isPublished(), trueIcon: FilamentIcon::resolve('inspirecms::visible'), falseIcon: FilamentIcon::resolve('inspirecms::invisiable'), falseColor: 'gray');
                    }),

                Forms\Components\Placeholder::make('display_published_at')
                    ->content(fn (Model | ModelsContent | null $record) => $record->getPublishTime())
                    ->label(__('inspirecms::resources/content.published_at.label'))
                    ->inlineLabel(),

                Forms\Components\Placeholder::make('display_latest_published_at')
                    ->content(fn (Model | ModelsContent | null $record) => $record->getLatestPublishedTime())
                    ->label(__('inspirecms::resources/content.latest_published_at.label'))
                    ->inlineLabel(),

                Forms\Components\Placeholder::make('display_status')
                    ->label(__('inspirecms::resources/content.status.label'))
                    ->inlineLabel()
                    ->content(function (Model | ModelsContent | null $record) {
                        if (is_null($record)) {
                            return null;
                        }

                        $status = inspirecms_content_statuses()->getOption($record->status);

                        if (! $status) {
                            return null;
                        }

                        return UIHelper::generateBadge($status->getLabel(), $status->getColor() ?? 'gray', $status->getIcon());
                    }),
            ])
            ->columns(['default' => 1]);
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getLockDetailGroupedFormComponent()
    {
        return Forms\Components\Section::make()
            ->visible(fn (null | Model | ModelsContent $record) => $record != null && $record->isLocked())
            ->schema([
                Forms\Components\Placeholder::make('display_locked_at')
                    ->content(fn (Model | ModelsContent $record) => $record->locked?->locked_at->diffForHumans())
                    ->label(__('inspirecms::resources/content.locked_at.label'))
                    ->inlineLabel(),
                Forms\Components\Placeholder::make('display_locked_by')
                    ->content(fn (Model | ModelsContent $record) => UIHelper::generateTextWithDescription(
                        text: $record->locked?->owner->name,
                        description: UIHelper::generateCopyableText(text: $record->locked?->owner->email)
                    ))
                    ->label(__('inspirecms::resources/content.locked_by.label'))
                    ->inlineLabel(),
            ]);
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    public static function getPublishedAtFormComponent()
    {
        return Forms\Components\DateTimePicker::make('published_at')
            ->label(__('inspirecms::resources/content.published_at.label'))
            ->native(false)
            ->prefixIcon('heroicon-m-calendar-date-range')
            ->suffixAction(ResetAction::make())
            ->hintIcon(FilamentIcon::resolve('inspirecms::info'), __('inspirecms::resources/content.published_at.hint'))
            ->default(now())
            ->required();
    }

    /** @return Forms\Components\Field | Forms\Components\Component */
    protected static function getDisplayKeyFormComponent()
    {
        return Forms\Components\Placeholder::make('display_id')
            ->label(__('inspirecms::inspirecms.id'))
            ->inlineLabel()
            ->visible(fn (null | Model | ModelsContent $record) => $record != null)
            ->content(fn (null | Model | ModelsContent $record) => UIHelper::generateCopyableText($record->getKey()));
    }

    /** @return Forms\Components\Field | Forms\Components\Component */
    protected static function getDisplayParentFormComponent()
    {
        return Forms\Components\Placeholder::make('display_parent')
            ->label(__('inspirecms::resources/content.parent.label'))
            ->inlineLabel()
            ->content(function (Model | ModelsContent | null $record, ContentForm $livewire) {
                if (is_null($record)) {
                    return null;
                }

                if ($record->isRootLevel()) {
                    return __('inspirecms::inspirecms.root');
                }

                $url = FilamentResourceHelper::attemptToGetUrl(
                    resource: static::class,
                    pages: ['view', 'edit'],
                    parameters: ['record' => $record->parent_id],
                    autorizeAction: false
                );

                return UIHelper::generateCopyableTextWithIconButton(
                    text: $record->parent_id,
                    icon: FilamentIcon::resolve('inspirecms::goto'),
                    color: 'gray',
                    size: 'sm',
                    url: $url,
                    linkTarget: '_blank',
                );
            });
    }

    /** @return Forms\Components\Field | Forms\Components\Component */
    protected static function getDisplayUrlFormComponent()
    {
        return Forms\Components\Placeholder::make('display_url')
            ->label(__('inspirecms::resources/content.url.label'))
            ->inlineLabel()
            ->content(function (Model | ModelsContent | null $record, ContentForm $livewire) {
                if (is_null($record)) {
                    return null;
                }

                $code = $livewire->getActiveActionsLocale();
                $lang = collect(InspireCms::getAllAvailableLanguages())->firstWhere(fn (LanguageDto $language) => $language->code === $code);

                $url = $record->getUrl($lang);

                if (is_null($url)) {
                    return null;
                }

                return UIHelper::generateCopyableTextWithIconButton(
                    text: $url,
                    icon: FilamentIcon::resolve('inspirecms::goto'),
                    color: 'gray',
                    size: 'sm',
                    url: $url,
                    linkTarget: '_blank',
                );
            });
    }

    /** @return Forms\Components\Field | Forms\Components\Component */
    protected static function getSeoFormComponent()
    {
        $langs = InspireCms::getAllAvailableLanguages();

        $configureTranslatableComponents = function (string $field, Closure $createFieldUsing) use ($langs) {

            $components = [];

            foreach ($langs as $lang) {

                $locale = $lang->code;

                $components[] = $createFieldUsing(
                    $field::make($locale)
                        ->visible(fn (ContentForm $livewire) => $livewire->getActiveActionsLocale() == $locale)
                        ->translatable()
                );
            }

            return $components;
        };
        $createSeoField = function ($key, $field, $callback) use ($configureTranslatableComponents) {

            if (in_array($key, SeoHelper::getTranslatableAttributes())) {
                return Forms\Components\Group::make()
                    ->statePath($key)
                    ->schema(
                        $configureTranslatableComponents(
                            $field,
                            $callback
                        )
                    );
            } else {
                return $callback($field::make($key));
            }
        };

        return Forms\Components\Group::make()
            ->columns(['md' => 3, 'default' => 1])
            ->dehydrated()
            ->relationship('webSetting')
            ->schema([
                Forms\Components\Section::make()
                    ->columns(1)
                    ->columnStart(['md' => 2])
                    ->statePath('seo')
                    ->schema(function () use ($createSeoField) {
                        $attribtues = [
                            'meta_title' => [
                                'field' => Forms\Components\TextInput::class,
                                'callback' => fn (Forms\Components\TextInput $field) => $field
                                    ->label(__('inspirecms::resources/content.seo.meta_title.label'))
                                    ->validationAttribute(__('inspirecms::resources/content.seo.meta_title.validation_attribute'))
                                    ->placeholder(__('inspirecms::resources/content.seo.meta_title.placeholder'))
                                    ->helperText(__('inspirecms::resources/content.seo.meta_title.instructions'))
                                    ->limitLengthWithHint(60),
                            ],
                            'meta_description' => [
                                'field' => Forms\Components\Textarea::class,
                                'callback' => fn (Forms\Components\Textarea $field) => $field
                                    ->label(__('inspirecms::resources/content.seo.meta_description.label'))
                                    ->validationAttribute(__('inspirecms::resources/content.seo.meta_description.validation_attribute'))
                                    ->placeholder(__('inspirecms::resources/content.seo.meta_description.placeholder'))
                                    ->helperText(__('inspirecms::resources/content.seo.meta_description.instructions'))
                                    ->limitLengthWithHint(120),
                            ],
                            'meta_keywords' => [
                                'field' => Forms\Components\TagsInput::class,
                                'callback' => fn (Forms\Components\TagsInput $field) => $field
                                    ->label(__('inspirecms::resources/content.seo.meta_keywords.label'))
                                    ->validationAttribute(__('inspirecms::resources/content.seo.meta_keywords.validation_attribute'))
                                    ->placeholder(__('inspirecms::resources/content.seo.meta_keywords.placeholder'))
                                    ->helperText(__('inspirecms::resources/content.seo.meta_keywords.instructions')),
                            ],
                        ];

                        $components = [];
                        foreach ($attribtues as $key => $attribute) {
                            $components[] = $createSeoField($key, $attribute['field'], $attribute['callback']);
                        }

                        return $components;
                    }),
                Forms\Components\Section::make()
                    ->columns(1)
                    ->heading(__('inspirecms::resources/content.sections.seo_og.heading'))
                    ->aside()
                    ->statePath('seo')
                    ->schema(function () use ($createSeoField) {
                        $attribtues = [
                            'og_title' => [
                                'field' => Forms\Components\TextInput::class,
                                'callback' => fn (Forms\Components\TextInput $field) => $field
                                    ->label(__('inspirecms::resources/content.seo.og_title.label'))
                                    ->validationAttribute(__('inspirecms::resources/content.seo.og_title.validation_attribute'))
                                    ->placeholder(__('inspirecms::resources/content.seo.og_title.placeholder'))
                                    ->helperText(__('inspirecms::resources/content.seo.og_title.instructions'))
                                    ->limitLengthWithHint(60),
                            ],
                            'og_description' => [
                                'field' => Forms\Components\Textarea::class,
                                'callback' => fn (Forms\Components\Textarea $field) => $field
                                    ->label(__('inspirecms::resources/content.seo.og_description.label'))
                                    ->validationAttribute(__('inspirecms::resources/content.seo.og_description.validation_attribute'))
                                    ->placeholder(__('inspirecms::resources/content.seo.og_description.placeholder'))
                                    ->helperText(__('inspirecms::resources/content.seo.og_description.instructions'))
                                    ->limitLengthWithHint(120),
                            ],
                            'og_image' => [
                                'field' => MediaPicker::class,
                                'callback' => fn (MediaPicker $field) => $field
                                    ->label(__('inspirecms::resources/content.seo.og_image.label'))
                                    ->validationAttribute(__('inspirecms::resources/content.seo.og_image.validation_attribute'))
                                    ->helperText(__('inspirecms::resources/content.seo.og_image.instructions'))
                                    ->image()
                                    ->max(1),
                            ],
                        ];

                        $components = [];
                        foreach ($attribtues as $key => $attribute) {
                            $components[] = $createSeoField($key, $attribute['field'], $attribute['callback']);
                        }

                        return $components;
                    }),
                Forms\Components\Section::make()
                    ->heading(__('inspirecms::resources/content.sections.robots.heading'))
                    ->aside()
                    ->statePath('robots')
                    ->schema([
                        Forms\Components\Toggle::make('noindex')
                            ->label(__('inspirecms::resources/content.robots.noindex.label'))
                            ->validationAttribute(__('inspirecms::resources/content.robots.noindex.validation_attribute'))
                            ->helperText(__('inspirecms::resources/content.robots.noindex.instructions')),
                        Forms\Components\Toggle::make('nofollow')
                            ->label(__('inspirecms::resources/content.robots.nofollow.label'))
                            ->validationAttribute(__('inspirecms::resources/content.robots.nofollow.validation_attribute'))
                            ->helperText(__('inspirecms::resources/content.robots.nofollow.instructions')),
                    ]),
                Forms\Components\Section::make()
                    ->heading(__('inspirecms::resources/content.sections.redirect.heading'))
                    ->aside()
                    ->schema([
                        Forms\Components\TextInput::make('redirect_path')
                            ->label(__('inspirecms::resources/content.redirect.redirect_path.label'))
                            ->validationAttribute(__('inspirecms::resources/content.redirect.redirect_path.validation_attribute'))
                            ->placeholder(__('inspirecms::resources/content.redirect.redirect_path.placeholder'))
                            ->helperText(__('inspirecms::resources/content.redirect.redirect_path.instructions')),
                        ContentPicker::make('redirect_content_id')
                            ->label(__('inspirecms::resources/content.redirect.redirect_content.label'))
                            ->validationAttribute(__('inspirecms::resources/content.redirect.redirect_content.validation_attribute'))
                            ->placeholder(__('inspirecms::resources/content.redirect.redirect_content.placeholder'))
                            ->helperText(__('inspirecms::resources/content.redirect.redirect_content.instructions'))
                            ->dehydrateStateUsing(fn ($state) => $state[0] ?? KeyHelper::generateMinUuid())
                            ->afterStateHydrated(function ($component, $state) {

                                if (is_null($state) || $state == 0 || $state == KeyHelper::generateMinUuid()) {
                                    $component->state([]);
                                } elseif (is_string($state)) {
                                    $component->state([$state]);
                                } else {
                                    $component->state($state);
                                }
                            })
                            ->modifySelectActionSelectorUsing(function (Forms\Components\Field | Forms\Components\Component | ContentTree $selector, $livewire) {
                                if ($selector instanceof ContentTree) {
                                    if (($currRecord = $livewire?->getRecord()) && $currRecord != null) {
                                        $selector = $selector->whereKeyNot($currRecord instanceof Model ? $currRecord->getKey() : $currRecord);
                                    }
                                    $selector = $selector->where(new ContentTree\Filter\BuilderFilter('whereIsWebPage'));
                                }

                                return $selector;
                            })
                            ->maxItems(1)
                            ->minItems(0),
                        Forms\Components\Select::make('redirect_type')
                            ->label(__('inspirecms::resources/content.redirect.redirect_type.label'))
                            ->validationAttribute(__('inspirecms::resources/content.redirect.redirect_type.validation_attribute'))
                            ->placeholder(__('inspirecms::resources/content.redirect.redirect_type.placeholder'))
                            ->helperText(__('inspirecms::resources/content.redirect.redirect_type.instructions'))
                            ->options([
                                301 => __('inspirecms::resources/content.redirect.redirect_type.301'),
                                302 => __('inspirecms::resources/content.redirect.redirect_type.302'),
                            ]),
                    ]),
            ]);
    }

    /** @return Forms\Components\Field | Forms\Components\Component */
    protected static function getSitemapFormComponent()
    {
        return Forms\Components\Section::make()
            ->relationship('sitemap')
            ->schema([
                Forms\Components\Toggle::make('enable')
                    ->label(__('inspirecms::resources/content.sitemap.enable.label'))
                    ->validationAttribute(__('inspirecms::resources/content.sitemap.enable.validation_attribute'))
                    ->helperText(__('inspirecms::resources/content.sitemap.enable.instructions'))
                    ->inlineLabel()
                    ->afterStateHydrated(fn ($component, $state) => $component->state(is_null($state) ? true : $state)),
                Forms\Components\TextInput::make('priority')
                    ->label(__('inspirecms::resources/content.sitemap.priority.label'))
                    ->validationAttribute(__('inspirecms::resources/content.sitemap.priority.validation_attribute'))
                    ->placeholder(__('inspirecms::resources/content.sitemap.priority.placeholder'))
                    ->helperText(new HtmlString(__('inspirecms::resources/content.sitemap.priority.instructions')))
                    ->inlineLabel()
                    ->numeric()
                    ->inputMode('decimal')
                    ->maxValue(1)
                    ->minValue(0)
                    ->step(0.1)
                    ->afterStateHydrated(fn ($component, $state) => $component->state($state ?? 0.5))
                    ->dehydrateStateUsing(fn ($state) => $state ?? 0.5)
                    ->required(),
                Forms\Components\Select::make('change_frequency')
                    ->label(__('inspirecms::resources/content.sitemap.change_frequency.label'))
                    ->validationAttribute(__('inspirecms::resources/content.sitemap.change_frequency.validation_attribute'))
                    ->placeholder(__('inspirecms::resources/content.sitemap.change_frequency.placeholder'))
                    ->helperText(__('inspirecms::resources/content.sitemap.change_frequency.instructions'))
                    ->inlineLabel()
                    ->options(SitemapChangeFrequency::class)
                    ->afterStateHydrated(fn ($component, $state) => $component->state($state ?? SitemapChangeFrequency::Monthly->value))
                    ->dehydrateStateUsing(fn ($state) => $state ?? SitemapChangeFrequency::Monthly->value)
                    ->required(),
            ]);
    }
    // endregion Form field(s)/component(s)
}
