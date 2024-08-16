<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Enums\ComponentStatus;
use SolutionForest\InspireCms\Filament\Forms\Components\BelongsToParentSelect;
use SolutionForest\InspireCms\Filament\Resources\Contents\PageResource\Pages;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class PageResource extends Resource
{
    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\Section::make()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Forms\Components\Grid::make(2)
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
                            ]),
                        BelongsToParentSelect::make('parent_page_id')
                            ->label(__('inspirecms::inspirecms.parent'))
                            ->nestableParentRelationship(name: 'parent', titleAttribute: 'title', ignoreRecord: true)
                            ->searchable(['title', 'slug'])
                            ->preload()
                            ->placeholder('(' . strtolower(__('inspirecms::inspirecms.no_parent') . ')'))
                            ->live(),

                        static::documentTypeSelect('document_type_id')
                            // Load field group from page type
                            ->live(debounce: 300)
                            ->afterStateUpdated(fn ($component) => $component
                                ->getContainer()                        // this field container
                                ->getParentComponent()                  // section
                                ->getContainer()                        // section's container
                                ->getComponent('dynamicFieldGroups')    // find component by unique key in same level with section's container
                                ->getChildComponentContainer()          // a container of "dynamicFieldGroups" fi-component
                                ->fill()),
                    ]),

                // Field group grouped component
                Forms\Components\Group::make()
                    ->key('dynamicFieldGroups')
                    ->columnSpanFull()
                    ->schema(function (Forms\Get $get) {
                        $documentTypeKey = $get('document_type_id');
                        if (empty($documentTypeKey)) {
                            return [];
                        }
                        $documentType = InspireCmsConfig::getDocumentTypeModelClass()::query()
                            ->with(['fieldGroups'])
                            ->whereHas('fieldGroups')
                            ->find($documentTypeKey);
                        if (! $documentType) {
                            return [];
                        }
                        $documentTypes = collect($documentType->ancestors())->push($documentType);
                        $componentsSchema = $documentTypes->pluck('fieldGroups')
                            ->flatMap(
                                fn ($fieldGroups) => collect($fieldGroups)
                                    ->map(fn ($fieldGroup) => $fieldGroup?->toFilamentComponent() ?? null)
                            )
                            ->filter()
                            ->toArray();

                        return $componentsSchema;
                    })
                    ->saveRelationshipsUsing(function ($record, $state) {
                        ray($record, $state)->label('todo versioning');
                    }),
            ]);
    }

    public static function detailInfoForm(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->label(__('inspirecms::inspirecms.slug'))
                            ->live(debounce: 300)->afterStateUpdated(fn ($component, $state) => $component->state(Str::slug($state)))
                            ->unique(column: 'slug', ignoreRecord: true, modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule, callable $get) {
                                $parentId = $get('parent_page_id');
                                if ($parentId) {
                                    return $rule->where('parent_page_id', $parentId);
                                } else {
                                    return $rule->whereNull('parent_page_id');
                                }
                            })
                            ->required(),
                    ]),
                // Versioning
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label(__('inspirecms::inspirecms.publish_at'))
                            ->native(false),
                        Forms\Components\Select::make('status')
                            ->label(__('inspirecms::inspirecms.status'))
                            ->options(ComponentStatus::class)
                            ->default(ComponentStatus::Draft->value)
                            ->live()->afterStateUpdated(function ($state, Forms\Set $set, $operation) {
                                // fill publish time as now is the status change to "Published"
                                if ($state == ComponentStatus::Published->value) {
                                    $set('published_at', now());
                                }
                                // reset state if creating
                                elseif ($operation == 'create' && $state == ComponentStatus::Draft->value) {
                                    $set('published_at', '');
                                }
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')->width('1%')->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::inspirecms.title'))
                    ->sortable(),
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

    /**
     * @return Forms\Components\Field | Forms\Components\Select
     */
    protected static function documentTypeSelect(string $name = 'document_type_id')
    {
        $parentFieldName = 'parent_page_id';

        return Forms\Components\Select::make($name)
            ->label(__('inspirecms::inspirecms.document_type'))
            ->searchable(['id'])
            ->preload()
            ->relationship(name: 'documentType', titleAttribute: 'title', modifyQueryUsing: function ($query, $get) use ($parentFieldName) {
                $isThisPageInRoot = $get($parentFieldName) == null;
                if ($isThisPageInRoot) {
                    return $query->whereNull('parent_id');
                } else {
                    return $query->whereNotNull('parent_id');
                }
            })
            ->required();
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getPageModelClass();
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
