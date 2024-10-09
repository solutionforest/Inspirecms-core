<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources;

use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Pboivin\FilamentPeek\Livewire\BuilderEditor;
use SolutionForest\InspireCms\Base\Enums\Frequency;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Dtos\LanguageDto;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\ContentForm;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource\Pages\ViewPage;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages\BaseContentListTrashPage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Forms\Components\Actions\ResetAction;
use SolutionForest\InspireCms\Filament\Forms\Components\TimestampsGroup;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Helpers\KeyHelper;
use SolutionForest\InspireCms\Helpers\UIHelper;
use SolutionForest\InspireCms\Models\Contracts\Content as ModelsContent;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

abstract class BaseContentResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;
    use Translatable;

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

    public static function getTranslatableLocales(): array
    {
        return array_keys(InspireCms::getAllAvailableLanguages());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Actions::make([
                    \Pboivin\FilamentPeek\Forms\Actions\InlinePreviewAction::make()
                        ->label(__('inspirecms::actions.preview.label'))
                        ->builderName('propertyData'),
                ])
                    ->alignEnd()
                    ->hidden(fn ($livewire) => $livewire instanceof ViewPage),
                Forms\Components\Tabs::make()
                    ->persistTabInQueryString()
                    ->contained(false)
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('seo')
                            ->label(__('inspirecms::resources/content.seo.heading'))
                            ->schema([
                                Forms\Components\Section::make()
                                    ->columns(1)
                                    ->heading(__('inspirecms::resources/content.general.heading'))
                                    ->aside()
                                    ->schema([
                                        static::getTitleFormComponent(),
                                        static::getSlugFormComponent(),
                                    ]),
                                static::getSeoFormComponent(),
                            ]),
                        static::getPropertyDataValueComponent(isTab: true),
                        Forms\Components\Tabs\Tab::make('siteMap')
                            ->label(__('inspirecms::resources/content.sitemap.heading'))
                            ->schema([
                                static::getSitemapFormComponent(),
                            ]),
                        Forms\Components\Tabs\Tab::make('details')
                            ->label(__('inspirecms::resources/content.details.heading'))
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
                                        Forms\Components\Section::make([
                                            static::getDocumentTypeDisplayComponent(),
                                            Forms\Components\Placeholder::make('id')
                                                ->label(__('inspirecms::inspirecms.id'))
                                                ->inlineLabel()
                                                ->visible(fn ($record) => $record != null)
                                                ->content(fn (Model | ModelsContent | null $record) => $record->getKey()),
                                        ]),
                                        Forms\Components\Group::make()
                                            ->visible(fn ($record) => $record != null)
                                            ->schema([
                                                static::getTimestampsGroupedFormComponent()->columnSpan(1),
                                                static::getPublishDetailGroupedFormComponent()->columnSpan(1),
                                            ]),
                                    ]),
                            ]),
                    ]),
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
                    ->afterStateHydrated(fn (ContentForm $livewire, $component) => $component->state($livewire->getPublishableFormDataBeforePublish([]))),
            ]);
    }

    public static function getPreviewBuilderEditorSchema(string $builderName): Forms\Components\Component | array
    {
        $langs = collect(InspireCms::getAllAvailableLanguages())
            ->mapWithKeys(fn (LanguageDto $lang) => [$lang->code => $lang->name])
            ->all();

        return [
            Forms\Components\Select::make('activeLocale')
                ->options($langs)
                ->afterStateHydrated(fn ($component) => $component->state(array_key_first($langs)))
                ->selectablePlaceholder(false)
                ->prefixIcon('heroicon-m-language')
                ->hiddenLabel()
                ->live(),
            static::getPropertyDataValueComponent(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id'))
                    ->width('1%')->sortable(),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label(__('inspirecms::inspirecms.deleted_at'))
                    ->sortable()
                    ->formatStateUsing(fn (?\Carbon\Carbon $state) => $state?->diffForHumans())
                    ->visibleOn([BaseContentListTrashPage::class])
                    ->width('5%'),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::inspirecms.title'))
                    ->sortable()
                    ->grow(),
                Tables\Columns\TextColumn::make('parent')
                    ->label(__('inspirecms::inspirecms.parent'))
                    ->getStateUsing(function ($record) {
                        if ($record->isRoot()) {
                            return null;
                        }

                        return $record->parent?->title ?? $record->parent_id;
                    })
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
                            ->alignCenter()->verticallyAlignCenter()
                            ->hiddenOn([BaseContentListTrashPage::class]),

                        Tables\Columns\TextColumn::make('published_at')
                            ->label(__('inspirecms::inspirecms.publish_at'))
                            ->getStateUsing(fn (ModelsContent $record) => $record->getLatestPublishedContentVersion()?->pivot->published_at?->diffForHumans())
                            ->width('5%')
                            ->hiddenOn([BaseContentListTrashPage::class]),
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
                Tables\Actions\EditAction::make()->iconButton()->visible(fn ($record) => ! $record->trashed()),
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
        return [];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getContentModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.content');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'publishedVersions', // To get published version, and determine is published
            'documentType', // For template use
            'parent', // To get parent title
        ]);
    }

    public static function resolveRecordRouteBinding(int | string $key): ?Model
    {
        return app(static::getModel())
            ->resolveRouteBindingQuery(static::getEloquentQuery()->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]), $key, static::getRecordRouteKeyName())
            ->first();
    }

    //region Form field(s)/component(s)
    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTitleFormComponent()
    {
        return Forms\Components\TextInput::make('title')
            ->label(__('inspirecms::resources/content.title.label'))
            ->placeholder(__('inspirecms::resources/content.title.placeholder'))
            ->helperText(__('inspirecms::resources/content.title.instructions'))
            ->live(true, 500)->afterStateUpdated(function ($state, $get, $set, $operation, ContentForm $livewire) {
                // Fill slug if empty / operation is create
                if ($operation === 'create' || empty($get('slug'))) {
                    $set('slug', Str::slug($state));
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
            ->placeholder(__('inspirecms::resources/content.slug.placeholder'))
            ->helperText(__('inspirecms::resources/content.slug.instructions'))
            ->live(true, 500)->afterStateUpdated(fn ($component, $state) => $component->state(Str::slug($state)))
            ->unique(table: static::getModel(), column: 'slug', ignoreRecord: true, modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule, callable $get, ContentForm $livewire, string $operation) {
                $model = new (static::getModel());

                $parentId = $get('parent_id') ?? null;

                if ($operation === 'create') {
                    $parentId = $livewire->getParentKey() ?? $parentId;
                }

                if (! filled($parentId)) {
                    $parentId = $model->getNestableRootValue();
                }

                return $rule
                    ->where('parent_id', $parentId);
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

        return Forms\Components\Hidden::make('parent_id')
            ->dehydratedWhenHidden()
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
    protected static function getDocumentTypeDisplayComponent()
    {
        return Forms\Components\Placeholder::make('document_type')
            ->label(__('inspirecms::inspirecms.document_type'))
            ->inlineLabel()
            ->content(function (Model | ModelsContent | null $record, ContentForm $livewire) {
                if ($record) {
                    $documentType = $record->documentType;
                    $text = $documentType->title;
                } else {
                    $documentType = $livewire->getDocumentType();
                    if (! $documentType instanceof Model) {
                        $documentType = InspireCmsConfig::getDocumentTypeModelClass()::find($documentType);
                    }
                    $text = $documentType?->title;
                }

                if (! filled($text)) {
                    $text = __('inspirecms::inspirecms.n/a');
                }
                $documentTypeKey = $documentType?->getKey();
                $resource = config('inspirecms.filament.resources.document_type', DocumentTypeResource::class);
                $url = $documentTypeKey ? FilamentResourceHelper::attemptToGetUrl($resource, ['edit', 'view'], ['record' => $documentTypeKey], false) : null;
                if (! $url) {
                    return $text;
                }

                return UIHelper::getInlineTextWithIconButtonPlaceholder($text, FilamentIcon::resolve('inspirecms::goto'), 'gray', 'sm', 'mr-2', $url);
            });
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getPropertyDataValueComponent(bool $isTab = false)
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

        $getFieldGroupsFromLivewireOrRecord = function (ContentForm | BuilderEditor $livewire, $record) use ($getFieldGroupsFromDocumentType) {
            if ($record) {
                $fieldGroups = $record->documentType->fieldGroups;
            } elseif ($livewire instanceof ContentForm) {
                $fieldGroups = $getFieldGroupsFromDocumentType($livewire->getDocumentType() ?? null);
            } elseif ($livewire instanceof BuilderEditor) {
                $fieldGroups = $getFieldGroupsFromDocumentType($livewire->editorData['documentType'] ?? null);
            } else {
                $fieldGroups = collect();
            }

            return $fieldGroups;
        };

        $schema = function (ContentForm | BuilderEditor $livewire, $record) use ($getFieldGroupsFromLivewireOrRecord) {
            $fieldGroups = $getFieldGroupsFromLivewireOrRecord($livewire, $record);

            $groupComponents = [];

            foreach ($fieldGroups as $fieldGroupModel) {

                $groupComponents[] = $fieldGroupModel->toFilamentComponent();
            }

            return $groupComponents;
        };

        if ($isTab) {

            return Forms\Components\Tabs\Tab::make('content')
                ->label(__('inspirecms::resources/content.content.heading'))
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
                static::getDisplayIsPublishedFormComponent(),
                static::getPublishedAtFormComponent(),
                static::getDisplayStatusFormComponent(),
            ])
            ->columns(['default' => 1]);
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getPublishedAtFormComponent()
    {
        return Forms\Components\Placeholder::make('published_at')
            ->content(fn (Model | ModelsContent | null $record) => $record->getLatestPublishedContentVersion()?->pivot->published_at)
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

    /** @return Forms\Components\Field | Forms\Components\Component */
    protected static function getDisplayStatusFormComponent()
    {
        return Forms\Components\Placeholder::make('display_status')
            ->label(__('inspirecms::inspirecms.status'))
            ->inlineLabel()
            ->content(function (Model | ModelsContent | null $record) {
                if (is_null($record)) {
                    return null;
                }

                $status = inspirecms_content_statuses()->getOption($record->status);

                if (! $status) {
                    return null;
                }

                return UIHelper::getBadgePlaceholder($status->getLabel(), $status->getColor(), $status->getIcon());
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

        return Forms\Components\Group::make()
            ->columns(['md' => 3, 'default' => 1])
            ->dehydrated()
            ->relationship('webSetting')
            ->schema([
                Forms\Components\Section::make()
                    ->columns(1)
                    ->columnStart(['md' => 2])
                    ->statePath('seo')
                    ->schema([
                        Forms\Components\Group::make()
                            ->statePath('meta_title')
                            ->schema(
                                $configureTranslatableComponents(
                                    Forms\Components\TextInput::class,
                                    fn (Forms\Components\TextInput $field) => $field
                                        ->label(__('inspirecms::resources/content.seo.meta_title.label'))
                                        ->placeholder(__('inspirecms::resources/content.seo.meta_title.placeholder'))
                                        ->helperText(__('inspirecms::resources/content.seo.meta_title.instructions'))
                                        ->limitLengthWithHint(60)
                                )
                            ),
                        Forms\Components\Group::make()
                            ->statePath('meta_description')
                            ->schema(
                                $configureTranslatableComponents(
                                    Forms\Components\Textarea::class,
                                    fn (Forms\Components\Textarea $field) => $field
                                        ->label(__('inspirecms::resources/content.seo.meta_description.label'))
                                        ->placeholder(__('inspirecms::resources/content.seo.meta_description.placeholder'))
                                        ->helperText(__('inspirecms::resources/content.seo.meta_description.instructions'))
                                        ->limitLengthWithHint(120)
                                )
                            ),
                        Forms\Components\TagsInput::make('meta_keywords')
                            ->label(__('inspirecms::resources/content.seo.meta_keywords.label'))
                            ->placeholder(__('inspirecms::resources/content.seo.meta_keywords.placeholder'))
                            ->helperText(__('inspirecms::resources/content.seo.meta_keywords.instructions')),
                    ]),
                Forms\Components\Section::make()
                    ->columns(1)
                    ->heading(__('inspirecms::resources/content.seo.og.heading'))
                    ->aside()
                    ->statePath('seo')
                    ->schema([

                        Forms\Components\Group::make()
                            ->statePath('og_title')
                            ->schema(
                                $configureTranslatableComponents(
                                    Forms\Components\TextInput::class,
                                    fn (Forms\Components\TextInput $field) => $field
                                        ->label(__('inspirecms::resources/content.seo.og.og_title.label'))
                                        ->placeholder(__('inspirecms::resources/content.seo.og.og_title.placeholder'))
                                        ->helperText(__('inspirecms::resources/content.seo.og.og_title.instructions'))
                                        ->limitLengthWithHint(60)
                                )
                            ),
                        Forms\Components\Group::make()
                            ->statePath('og_description')
                            ->schema(
                                $configureTranslatableComponents(
                                    Forms\Components\Textarea::class,
                                    fn (Forms\Components\Textarea $field) => $field
                                        ->label(__('inspirecms::resources/content.seo.og.og_description.label'))
                                        ->placeholder(__('inspirecms::resources/content.seo.og.og_description.placeholder'))
                                        ->helperText(__('inspirecms::resources/content.seo.og.og_description.instructions'))
                                        ->limitLengthWithHint(120)
                                )
                            ),
                        Forms\Components\FileUpload::make('og_image')
                            ->label(__('inspirecms::resources/content.seo.og.og_image.label'))
                            ->placeholder(__('inspirecms::resources/content.seo.og.og_image.placeholder'))
                            ->helperText(__('inspirecms::resources/content.seo.og.og_image.instructions'))
                            ->image(),
                    ]),
                Forms\Components\Section::make()
                    ->heading(__('inspirecms::resources/content.seo.robots.heading'))
                    ->aside()
                    ->statePath('robots')
                    ->schema([
                        Forms\Components\Toggle::make('noindex')
                            ->label(__('inspirecms::resources/content.seo.robots.noindex.label'))
                            ->helperText(__('inspirecms::resources/content.seo.robots.noindex.instructions')),
                        Forms\Components\Toggle::make('nofollow')
                            ->label(__('inspirecms::resources/content.seo.robots.nofollow.label'))
                            ->helperText(__('inspirecms::resources/content.seo.robots.nofollow.instructions')),
                    ]),
                Forms\Components\Section::make()
                    ->heading(__('inspirecms::resources/content.redirect.heading'))
                    ->aside()
                    ->schema([
                        Forms\Components\TextInput::make('redirect_path')
                            ->label(__('inspirecms::resources/content.redirect.redirect_path.label'))
                            ->placeholder(__('inspirecms::resources/content.redirect.redirect_path.placeholder'))
                            ->helperText(__('inspirecms::resources/content.redirect.redirect_path.instructions')),
                        \SolutionForest\InspireCms\Filament\Forms\Components\ContentPicker::make('redirect_content_id')
                            ->label(__('inspirecms::resources/content.redirect.redirect_content.label'))
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
                            ->exceptRecord(fn ($livewire) => $livewire?->getRecord())
                            ->maxItems(1),
                        Forms\Components\Select::make('redirect_type')
                            ->label(__('inspirecms::resources/content.redirect.redirect_type.label'))
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
    protected static function getSiteMapFormComponent()
    {
        return Forms\Components\Section::make()
            ->relationship('sitemap')
            ->schema([
                Forms\Components\Toggle::make('enable')
                    ->label(__('inspirecms::resources/content.sitemap.enable.label'))
                    ->helperText(__('inspirecms::resources/content.sitemap.enable.instructions'))
                    ->inlineLabel()
                    ->afterStateHydrated(fn ($component, $state) => $component->state(is_null($state) ? true : $state)),
                Forms\Components\TextInput::make('priority')
                    ->label(__('inspirecms::resources/content.sitemap.priority.label'))
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
                    ->placeholder(__('inspirecms::resources/content.sitemap.change_frequency.placeholder'))
                    ->helperText(__('inspirecms::resources/content.sitemap.change_frequency.instructions'))
                    ->inlineLabel()
                    ->options(Frequency::class)
                    ->afterStateHydrated(fn ($component, $state) => $component->state($state ?? Frequency::Monthly->value))
                    ->dehydrateStateUsing(fn ($state) => $state ?? Frequency::Monthly->value)
                    ->required(),
            ]);
    }
    //endregion Form field(s)/component(s)
}
