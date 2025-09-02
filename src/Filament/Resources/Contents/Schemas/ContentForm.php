<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\Schemas;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Unique;
use Pboivin\FilamentPeek\Forms\Actions\InlinePreviewAction;
use SolutionForest\InspireCms\Base\Enums\DocumentTypeCategory;
use SolutionForest\InspireCms\Base\Enums\SitemapChangeFrequency;
use SolutionForest\InspireCms\Base\Filament\Contracts\ContentForm as ContractsContentForm;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseContentViewPage;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Dtos\LanguageDto;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Factories\ContentSlugFactory;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentPicker;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentTree;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentTree\Filter\BuilderFilter;
use SolutionForest\InspireCms\Filament\Resources\Contents\Schemas\Components\ContentPropertyDataGroup;
use SolutionForest\InspireCms\Filament\Resources\Contents\Schemas\Components\ContentPublishedAtDateTimePicker;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Helpers\SeoHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content as ModelsContent;
use SolutionForest\InspireCms\Support\Helpers\KeyHelper;
use SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components\MediaPicker;

class ContentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
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
            ->components([
                Actions::make([
                    InlinePreviewAction::make()
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
                Tabs::make()
                    ->persistTabInQueryString()
                    ->contained(false)
                    ->tabs(function (ContractsContentForm $livewire) {

                        $documentType = $livewire->getDocumentType();

                        $tabs[] = ContentPropertyDataGroup::make(isTab: true);

                        if ($documentType->display_category != DocumentTypeCategory::Data) {
                            $tabs[] = Tab::make('seo')
                                ->label(__('inspirecms::resources/content.tabs.seo'))
                                ->columns(['default' => 1])
                                ->schema([
                                    Section::make()
                                        ->heading(__('inspirecms::resources/content.sections.general.heading'))
                                        ->aside()
                                        ->schema([
                                            static::getTitleFormComponent(),
                                            static::getSlugFormComponent(),
                                        ]),
                                    static::getSeoFormComponent(),
                                ]);

                            $tabs[] = Tab::make('sitemap')
                                ->label(__('inspirecms::resources/content.tabs.sitemap'))
                                ->schema([
                                    static::getSitemapFormComponent(),
                                ]);
                        }
                        $tabs[] = Tab::make('details')
                            ->label(__('inspirecms::resources/content.tabs.details'))
                            ->columns(3)
                            ->schema([
                                Section::make()
                                    ->columns(1)
                                    ->columnSpan(1)
                                    ->schema([
                                        static::getTemplateFormComponent(),
                                        static::getParentPageFormComponent(),
                                        static::getDocumentTypeFormComponent(),
                                    ]),
                                Group::make()
                                    ->columns(1)
                                    ->columnSpan(2)
                                    ->schema([
                                        ...(
                                            $documentType->display_category != DocumentTypeCategory::Data ?
                                            [] :
                                            [
                                                Section::make([
                                                    static::getTitleFormComponent(),
                                                    static::getSlugFormComponent(),
                                                ]),
                                            ]
                                        ),
                                        Section::make([
                                            static::getDisplayDocumentTypeTextEntry(),
                                            static::getDisplayParentTextEntry(),
                                            static::getDisplayIdTextEntry(),
                                            static::getDisplayUrlTextEntry()
                                                ->visible($documentType->display_category != DocumentTypeCategory::Data),
                                        ]),
                                        Group::make()
                                            ->visible(fn ($record) => $record != null)
                                            ->columns(['default' => 1])
                                            ->schema([
                                                static::getTimestampsGroupedFormComponent(),
                                                static::getPublishDetailGroupedFormComponent(),
                                                static::getLockDetailGroupedFormComponent(),
                                            ]),
                                    ]),
                            ]);

                        return $tabs;
                    }),
            ]);
    }

    /**
     * @return Field|Component
     */
    protected static function getTitleFormComponent()
    {
        return TextInput::make('title')
            ->label(__('inspirecms::resources/content.title.label'))
            ->validationAttribute(__('inspirecms::resources/content.title.validation_attribute'))
            ->placeholder(__('inspirecms::resources/content.title.placeholder'))
            ->helperText(__('inspirecms::resources/content.title.instructions'))
            ->live(true, 5000)->afterStateUpdated(function ($state, $get, $set, $operation, ContractsContentForm $livewire) {
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
     * @return Field|Component
     */
    protected static function getSlugFormComponent()
    {
        return TextInput::make('slug')
            ->label(__('inspirecms::resources/content.slug.label'))
            ->validationAttribute(__('inspirecms::resources/content.slug.validation_attribute'))
            ->placeholder(__('inspirecms::resources/content.slug.placeholder'))
            ->helperText(__('inspirecms::resources/content.slug.instructions'))
            ->live(true, 5000)
            ->afterStateUpdated(function ($component, $state) {
                return $component->state(ContentSlugFactory::create()->generate($state));
            })
            ->unique(table: InspireCmsConfig::getContentModelClass(), column: 'slug', ignoreRecord: true, modifyRuleUsing: function (Unique $rule, callable $get, ContractsContentForm $livewire, string $operation) {
                $model = new (InspireCmsConfig::getContentModelClass());

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
     * @return Field|Component
     */
    protected static function getParentPageFormComponent()
    {
        $fallbackParentId = KeyHelper::generateMinUuid();

        return Hidden::make('parent_id')
            ->dehydratedWhenHidden()
            ->dehydrateStateUsing(function (ContractsContentForm $livewire, $operation, null | Model | ModelsContent $record) use ($fallbackParentId) {
                if ($operation === 'create') {
                    return $livewire->getParentKey() ?? $fallbackParentId;
                }

                return $record?->parent_id ?? $fallbackParentId;
            });
    }

    /**
     * @return Field|Component
     */
    protected static function getTemplateFormComponent()
    {
        return Select::make('template_id')
            ->label(__('inspirecms::resources/content.template.label'))
            ->validationAttribute(__('inspirecms::resources/content.template.validation_attribute'))
            ->helperText(__('inspirecms::resources/content.template.instructions'))
            ->options(function (ContractsContentForm $livewire) {
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
     * @return Field|Select
     */
    protected static function getDocumentTypeFormComponent()
    {
        return Hidden::make('document_type_id')
            ->validationAttribute(__('inspirecms::resources/content.document_type.validation_attribute'))
            ->dehydratedWhenHidden()
            ->dehydrateStateUsing(function (ContractsContentForm $livewire, null | Model | ModelsContent $record) {
                return $record?->document_type_id ?? $livewire->getDocumentType()?->getKey();
            });
    }

    /**
     * @return TextEntry
     */
    protected static function getDisplayDocumentTypeTextEntry()
    {
        return TextEntry::make('display_document_type')
            ->label(__('inspirecms::resources/content.document_type.label'))
            ->inlineLabel()
            ->placeholder(__('inspirecms::inspirecms.n/a'))
            ->state(function (Model | ModelsContent | null $record, ContractsContentForm $livewire) {
                return ($record?->documentType ?? $livewire->getDocumentType())?->title;
            })
            ->url(function (Model | ModelsContent | null $record, ContractsContentForm $livewire) {
                $documentType = $record?->documentType ?? $livewire->getDocumentType();
                if (! $documentType) {
                    return null;
                }

                return FilamentResourceHelper::attemptToGetUrl(
                    resource: InspireCmsConfig::getFilamentResource('document_type', DocumentTypeResource::class),
                    pages: ['edit', 'view'],
                    parameters: ['record' => $documentType],
                    autorizeAction: true
                );
            }, true);
    }

    /**
     * @return Field|Component
     */
    protected static function getTimestampsGroupedFormComponent()
    {
        return Section::make()
            ->schema([
                TextEntry::make('created_at')
                    ->dateTime()
                    ->label(__('inspirecms::inspirecms.created_at'))
                    ->inlineLabel(),

                TextEntry::make('updated_at')
                    ->dateTime()
                    ->label(__('inspirecms::inspirecms.last_updated_at'))
                    ->inlineLabel(),

                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->label(__('inspirecms::inspirecms.deleted_at'))
                    ->inlineLabel()
                    ->weight('bold')
                    ->color('danger')
                    ->icon(FilamentIcon::resolve('inspirecms::recycle_bin'))
                    ->iconColor('danger')
                    ->iconPosition('after')
                    ->visible(function (?Model $record) {

                        if (! $record) {
                            return null;
                        }

                        $traits = class_uses_recursive($record);

                        if (! in_array(SoftDeletes::class, $traits)) {
                            return null;
                        }

                        return $record->trashed();
                    }),
            ])
            ->columns(['default' => 1])
            ->visible(fn ($record) => $record != null);
    }

    /**
     * @return Field|Component
     */
    protected static function getPublishDetailGroupedFormComponent()
    {
        return Section::make()
            ->schema([

                IconEntry::make('display_is_published')
                    ->label(__('inspirecms::resources/content.is_published.label'))
                    ->inlineLabel()
                    ->state(function (Model | ModelsContent | null $record) {
                        if (is_null($record)) {
                            return null;
                        }

                        return $record->isPublished();
                    })
                    ->trueIcon(FilamentIcon::resolve('inspirecms::visible'))
                    ->falseIcon(FilamentIcon::resolve('inspirecms::invisiable'))
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextEntry::make('display_published_at')
                    ->dateTime()
                    ->state(fn (Model | ModelsContent | null $record) => $record->getPublishTime())
                    ->label(__('inspirecms::resources/content.published_at.label'))
                    ->inlineLabel(),

                TextEntry::make('display_latest_published_at')
                    ->dateTime()
                    ->state(fn (Model | ModelsContent | null $record) => $record->getLatestPublishedTime())
                    ->label(__('inspirecms::resources/content.latest_published_at.label'))
                    ->inlineLabel(),

                TextEntry::make('display_status')
                    ->label(__('inspirecms::resources/content.status.label'))
                    ->inlineLabel()
                    ->state(function (Model | ModelsContent | null $record) {
                        if (is_null($record)) {
                            return null;
                        }

                        $status = inspirecms_content_statuses()->getOption($record->status);

                        return $status;
                    })
                    ->badge()
                    ->formatStateUsing(fn ($state) => strval($state && $state instanceof ContentStatusOption ? $state->getLabel() : $state))
                    ->color(fn ($state) => $state && $state instanceof ContentStatusOption ? $state->getColor() : null)
                    ->icon(fn ($state) => $state && $state instanceof ContentStatusOption ? $state->getIcon() : null),
            ])
            ->columns(['default' => 1]);
    }

    /**
     * @return Field|Component
     */
    protected static function getLockDetailGroupedFormComponent()
    {
        return Section::make()
            ->visible(fn (null | Model | ModelsContent $record) => $record != null && $record->isLocked())
            ->schema([

                TextEntry::make('display_locked_at')
                    ->state(fn (null | Model | ModelsContent $record) => $record?->locked?->locked_at)
                    ->dateTime()
                    ->since()
                    ->label(__('inspirecms::resources/content.locked_at.label'))
                    ->inlineLabel(),

                TextEntry::make('display_locked_by')
                    ->state(fn (null | Model | ModelsContent $record) => $record?->locked?->owner->name)
                    ->belowContent(fn (null | Model | ModelsContent $record) => $record?->locked?->owner->email)
                    ->label(__('inspirecms::resources/content.locked_by.label'))
                    ->inlineLabel(),
            ]);
    }

    /**
     * @return Field|Component
     */
    public static function getPublishedAtFormComponent()
    {
        return ContentPublishedAtDateTimePicker::make();
    }

    /** @return TextEntry */
    protected static function getDisplayIdTextEntry()
    {
        return TextEntry::make('display_id')
            ->label(__('inspirecms::inspirecms.id'))
            ->inlineLabel()
            ->visible(fn (?Model $record) => $record != null)
            ->state(fn (?Model $record) => $record?->getKey())
            ->copyable();
    }

    /** @return TextEntry */
    protected static function getDisplayParentTextEntry()
    {
        return TextEntry::make('display_parent')
            ->label(__('inspirecms::resources/content.parent.label'))
            ->inlineLabel()
            ->placeholder(__('inspirecms::inspirecms.n/a'))
            ->state(function (Model | ModelsContent | null $record) {
                if (is_null($record)) {
                    return null;
                }

                if ($record->isRootLevel()) {
                    return __('inspirecms::inspirecms.root');
                }

                return $record->parent_id;
            })
            ->url(function (Model | ModelsContent | null $record) {
                if (is_null($record)) {
                    return null;
                }

                if ($record->isRootLevel()) {
                    return null;
                }

                return FilamentResourceHelper::attemptToGetUrl(
                    resource: static::class,
                    pages: ['view', 'edit'],
                    parameters: ['record' => $record->parent_id],
                    autorizeAction: false
                );
            }, true)
            ->copyable();
    }

    /** @return TextEntry */
    protected static function getDisplayUrlTextEntry()
    {
        return TextEntry::make('display_url')
            ->label(__('inspirecms::resources/content.url.label'))
            ->inlineLabel()
            ->state(function (Model | ModelsContent | null $record, ContractsContentForm $livewire) {
                if (is_null($record)) {
                    return null;
                }

                $code = $livewire->getActiveActionsLocale();
                $lang = collect(InspireCms::getAllAvailableLanguages())->firstWhere(fn (LanguageDto $language) => $language->code === $code);

                $url = $record->getUrl($lang);

                if (is_null($url)) {
                    return null;
                }

                return $url;
            })
            ->url(function ($state) {
                return $state;
            }, true)
            ->copyable();
    }

    /** @return Field|Component */
    protected static function getSeoFormComponent()
    {
        $langs = InspireCms::getAllAvailableLanguages();

        $configureTranslatableComponents = function (string $field, Closure $createFieldUsing) use ($langs) {

            $components = [];

            foreach ($langs as $lang) {

                $locale = $lang->code;

                $components[] = $createFieldUsing(
                    $field::make($locale)
                        ->visible(fn (ContractsContentForm $livewire) => $livewire->getActiveActionsLocale() == $locale)
                        ->translatable()
                );
            }

            return $components;
        };
        $createSeoField = function ($key, $field, $callback) use ($configureTranslatableComponents) {

            if (in_array($key, SeoHelper::getTranslatableAttributes())) {
                return Group::make()
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

        return Group::make()
            ->dehydrated()
            ->relationship('webSetting')
            ->columns(['default' => 1])
            ->schema([
                Section::make()
                    ->aside()
                    ->heading(str('&nbsp;')->toHtmlString())
                    ->statePath('seo')
                    ->schema(function () use ($createSeoField) {
                        $attribtues = [
                            'meta_title' => [
                                'field' => TextInput::class,
                                'callback' => fn (TextInput $field) => $field
                                    ->label(__('inspirecms::resources/content.seo.meta_title.label'))
                                    ->validationAttribute(__('inspirecms::resources/content.seo.meta_title.validation_attribute'))
                                    ->placeholder(__('inspirecms::resources/content.seo.meta_title.placeholder'))
                                    ->helperText(__('inspirecms::resources/content.seo.meta_title.instructions'))
                                    ->limitLengthWithHint(60),
                            ],
                            'meta_description' => [
                                'field' => Textarea::class,
                                'callback' => fn (Textarea $field) => $field
                                    ->label(__('inspirecms::resources/content.seo.meta_description.label'))
                                    ->validationAttribute(__('inspirecms::resources/content.seo.meta_description.validation_attribute'))
                                    ->placeholder(__('inspirecms::resources/content.seo.meta_description.placeholder'))
                                    ->helperText(__('inspirecms::resources/content.seo.meta_description.instructions'))
                                    ->limitLengthWithHint(120),
                            ],
                            'meta_keywords' => [
                                'field' => TagsInput::class,
                                'callback' => fn (TagsInput $field) => $field
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
                Section::make()
                    ->heading(__('inspirecms::resources/content.sections.seo_og.heading'))
                    ->aside()
                    ->statePath('seo')
                    ->schema(function () use ($createSeoField) {
                        $attribtues = [
                            'og_title' => [
                                'field' => TextInput::class,
                                'callback' => fn (TextInput $field) => $field
                                    ->label(__('inspirecms::resources/content.seo.og_title.label'))
                                    ->validationAttribute(__('inspirecms::resources/content.seo.og_title.validation_attribute'))
                                    ->placeholder(__('inspirecms::resources/content.seo.og_title.placeholder'))
                                    ->helperText(__('inspirecms::resources/content.seo.og_title.instructions'))
                                    ->limitLengthWithHint(60),
                            ],
                            'og_description' => [
                                'field' => Textarea::class,
                                'callback' => fn (Textarea $field) => $field
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
                Section::make()
                    ->heading(__('inspirecms::resources/content.sections.robots.heading'))
                    ->aside()
                    ->statePath('robots')
                    ->schema([
                        Toggle::make('noindex')
                            ->label(__('inspirecms::resources/content.robots.noindex.label'))
                            ->validationAttribute(__('inspirecms::resources/content.robots.noindex.validation_attribute'))
                            ->helperText(__('inspirecms::resources/content.robots.noindex.instructions')),
                        Toggle::make('nofollow')
                            ->label(__('inspirecms::resources/content.robots.nofollow.label'))
                            ->validationAttribute(__('inspirecms::resources/content.robots.nofollow.validation_attribute'))
                            ->helperText(__('inspirecms::resources/content.robots.nofollow.instructions')),
                    ]),
                Section::make()
                    ->heading(__('inspirecms::resources/content.sections.redirect.heading'))
                    ->aside()
                    ->schema([
                        TextInput::make('redirect_path')
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
                            ->modifySelectActionSelectorUsing(function (Field | Component | ContentTree $selector, $livewire) {
                                if ($selector instanceof ContentTree) {
                                    if (($currRecord = $livewire?->getRecord()) && $currRecord != null) {
                                        $selector = $selector->whereKeyNot($currRecord instanceof Model ? $currRecord->getKey() : $currRecord);
                                    }
                                    $selector = $selector->where(new BuilderFilter('whereIsWebPage'));
                                }

                                return $selector;
                            })
                            ->maxItems(1)
                            ->minItems(0),
                        Select::make('redirect_type')
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

    /** @return Field|Component */
    protected static function getSitemapFormComponent()
    {
        return Section::make()
            ->relationship('sitemap')
            ->schema([
                Toggle::make('enable')
                    ->label(__('inspirecms::resources/content.sitemap.enable.label'))
                    ->validationAttribute(__('inspirecms::resources/content.sitemap.enable.validation_attribute'))
                    ->helperText(__('inspirecms::resources/content.sitemap.enable.instructions'))
                    ->inlineLabel()
                    ->afterStateHydrated(fn ($component, $state) => $component->state(is_null($state) ? true : $state)),
                TextInput::make('priority')
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
                Select::make('change_frequency')
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
}
