<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
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
            'replicate',
        ];
    }

    protected static ?int $navigationSort = -10;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

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
                            static::getIsElementTypeFormComponent(),
                            static::getTimestampsGroupedFormComponent(),
                        ])
                        ->grow(false),
                    Forms\Components\Section::make()
                        ->columns(1)
                        ->schema([
                            static::getNameFormComponent()->inlineLabel()->columnSpanFull(),
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
                static::getNameFormComponent()->inlineLabel(),
                static::getIsElementTypeFormComponent(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('created_at', 'desc')
            ->emptyStateActions([])
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id'))
                    ->width('1%')->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('inspirecms::inspirecms.name'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_element_type')
                    ->label(__('inspirecms::inspirecms.is_element_type'))
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
                RelationManagers\ContentsRelationManager::class,
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

    //region Form field(s)/component(s)
    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getNameFormComponent()
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('inspirecms::inspirecms.name'))
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
                        'name' => strtolower(__('inspirecms::inspirecms.field_group')),
                    ]))
                    ->createOptionForm(function (Form $form) {

                        $fieldGroupResource = config('inspirecms.resources.field_group', FieldGroupResource::class);

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

                        $fieldGroupResource = config('inspirecms.resources.field_group', FieldGroupResource::class);

                        $itemData = $component->getItemState($arguments['item']);

                        if (! (
                            $fieldGroupResource::hasPage('edit') ||
                            is_array($itemData) ||
                            isset($itemData['field_group_id'])
                        )
                        ) {

                            return null;
                        }

                        try {

                            return $fieldGroupResource::getUrl('edit', [
                                'record' => $itemData['field_group_id'],
                            ]);

                        } catch (\Throwable $e) {

                            return null;

                        }
                    }, true),
            ])
            ->inlineLabel();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getIsElementTypeFormComponent()
    {
        return Forms\Components\Toggle::make('is_element_type')
            ->label(__('inspirecms::inspirecms.is_element_type'))
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
