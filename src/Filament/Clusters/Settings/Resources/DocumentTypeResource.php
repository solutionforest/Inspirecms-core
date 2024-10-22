<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Pages;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\RelationManagers;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Forms\Components\DocumentFieldGroup;
use SolutionForest\InspireCms\Filament\Forms\Components\RevertOrderGroup;
use SolutionForest\InspireCms\Filament\Forms\Components\TimestampsGroup;
use SolutionForest\InspireCms\Filament\Tables\Actions\CloneAction;
use SolutionForest\InspireCms\Filament\Tables\Actions\QuickEditAction;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Models\Contracts\DocumentType as CmsDocumentType;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class DocumentTypeResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'attach',
            'detach',
        ];
    }

    protected static ?int $navigationSort = -10;

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
                            static::getSlugFormComponent()->inlineLabel()->columnSpanFull(),
                            static::getIsWebPageFormComponent(),
                            static::getShowChildAsTableFormComponent(),
                            static::getTimestampsGroupedFormComponent(),
                        ])
                        ->grow(false),
                    Forms\Components\Section::make()
                        ->columns(1)
                        ->schema([
                            static::getTitleFormComponent()->inlineLabel()->columnSpanFull(),
                            static::getFieldGroupFormComponent(),
                        ])
                        ->grow(),
                ])->revertBreakPoint('lg'),
            ]);
    }

    public static function quickForm(Form $form): Form
    {
        return $form
            ->schema([
                static::getTitleFormComponent()->inlineLabel(),
                static::getSlugFormComponent()->inlineLabel(),
                static::getIsWebPageFormComponent(),
                static::getShowChildAsTableFormComponent(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateActions([])
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id'))
                    ->width('1%')->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::inspirecms.title')),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('inspirecms::inspirecms.slug'))
                    ->sortable()
                    ->badge(),
                Tables\Columns\IconColumn::make('is_web_page')
                    ->label(__('inspirecms::inspirecms.is_web_page'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('show_children_as_table')
                    ->label(__('inspirecms::inspirecms.show_children_as_table'))
                    ->boolean(),

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
                Tables\Actions\ActionGroup::make([

                    QuickEditAction::make(),

                    CloneAction::make()
                        ->recordTitleAttribute('title')
                        ->saveRelationshipsUsing(function (Model | CmsDocumentType $originalRecord, Model | CmsDocumentType $record) {

                            $fieldGroups = $originalRecord->fieldGroupables->map(fn (Model $originalFieldGroup) => $originalFieldGroup->replicate([
                                'model_type',
                                'model_id',
                            ])->toArray())->all();

                            $record->fieldGroupables()->createMany($fieldGroups);
                        }),
                ]),
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
            'index' => Pages\ListDocumentTypes::route('/'),
            'create' => Pages\CreateDocumentType::route('/create'),
            'edit' => Pages\EditDocumentType::route('/{record}/edit'),
            'view' => Pages\ViewDocumentType::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TemplatesRelationManager::class,
            RelationGroup::make(fn () => __('inspirecms::inspirecms.referenced_by'), [
                RelationManagers\ContentRelationManager::class,
            ]),
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getDocumentTypeModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.document_type');
    }

    //region Global search
    public static function getGloballySearchableAttributes(): array
    {
        return ['slug'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return new HtmlString(Blade::render(<<<'blade'
            <div class="flex gap-x-2 items-center">
                <span>{{ $title }}</span>
                <x-filament::badge>
                    {{ $badge }}
                </x-filament::badge>
            </div>
        blade, ['title' => static::getRecordTitle($record), 'badge' => $record->slug]));
    }
    //endregion Global search

    //region Form field(s)/component(s)
    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTitleFormComponent()
    {
        return Forms\Components\TextInput::make('title')
            ->label(__('inspirecms::inspirecms.title'))
            ->live(true, 300)->afterStateUpdated(function ($state, $get, $set, $operation) {
                // Fill slug if empty / operation is create
                if ($operation === 'create' || $operation === 'quick_create' || empty($get('slug'))) {
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
            ->live(true, 300)->afterStateUpdated(fn ($component, $state) => $component->state(Str::slug($state)))
            ->unique(table: static::getModel(), column: 'slug', ignoreRecord: true)
            ->required();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getFieldGroupFormComponent()
    {
        return DocumentFieldGroup::make()
            ->modifyFieldGroupSelectUsing(function (Forms\Components\Select $select) {
                return $select
                    ->createOptionModalHeading(fn () => __('inspirecms::inspirecms.create_xxx', [
                        'name' => __('inspirecms::inspirecms.field_group'),
                    ]))
                    ->createOptionForm(function (Form $form) {

                        $fieldGroupResource = config('inspirecms.filament.resources.field_group', FieldGroupResource::class);

                        if (method_exists($fieldGroupResource, 'quickForm')) {
                            return $fieldGroupResource::quickForm($form);
                        }

                        if (method_exists($fieldGroupResource, 'form')) {
                            return $fieldGroupResource::form($form);
                        }

                        return $form;
                    })
                    ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                        $action
                            ->modalWidth('3xl');
                    })
                    ->createOptionUsing(function (array $data) {

                        $createdFieldGroup = InspireCmsConfig::getFieldGroupModelClass()::create($data);

                        return $createdFieldGroup->getKey();
                    });
            })
            ->extraFieldGroupRepeaterItemActions([
                Forms\Components\Actions\Action::make('goToEdit')
                    ->icon(fn () => FilamentIcon::resolve('actions::edit-action') ?? 'heroicon-m-pencil-square')
                    ->hidden(fn ($action): bool => PermissionManifest::authorizeModel('update', InspireCmsConfig::getFieldGroupModelClass()) != true || blank($action->getUrl()))
                    ->url(function (array $arguments, Forms\Components\Repeater $component) {

                        $fieldGroupResource = config('inspirecms.filament.resources.field_group', FieldGroupResource::class);

                        $itemData = $component->getItemState($arguments['item']);

                        $url = FilamentResourceHelper::attemptToGetUrl($fieldGroupResource, ['edit'], [
                            'record' => $itemData['field_group_id'],
                        ], false);
                        if (! (
                            filled($url) ||
                            is_array($itemData) ||
                            isset($itemData['field_group_id'])
                        )
                        ) {

                            return null;
                        }

                        return $url;

                    }, true),
            ])
            ->inlineLabel();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getIsWebPageFormComponent()
    {
        return Forms\Components\Toggle::make('is_web_page')
            ->label(__('inspirecms::inspirecms.is_web_page'))
            ->inlineLabel()
            ->default(true)
            ->live();
    }

    /** @return Forms\Components\Field | Forms\Components\Component*/
    protected static function getShowChildAsTableFormComponent()
    {
        return Forms\Components\Toggle::make('show_children_as_table')
            ->label(__('inspirecms::inspirecms.show_children_as_table'))
            ->inlineLabel()
            ->default(false)
            ->live();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTimestampsGroupedFormComponent()
    {
        return TimestampsGroup::make()
            ->columns(['default' => 1]);
    }
    //endregion Form field(s)/component(s)
}
